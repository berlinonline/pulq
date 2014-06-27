<?php

namespace Pulq\Agavi\Database\ElasticSearch;

use Pulq\Agavi\Database\PulqDatabase;
use Elastica;
use Elastica\Status;
use Elastica\Search;
use Elastica\Document;
use \AgaviDatabaseException;
use \AgaviDatabaseManager;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;

class Database extends PulqDatabase
{
    const DEFAULT_HOST = 'localhost:9200';

    protected $connection;
    protected $index_config;

    public function initialize(AgaviDatabaseManager $database_manager, array $parameters = array())
    {
        parent::initialize($database_manager, $parameters);
        $this->index_config = $this->getParameter('index');

        $this->connect();
    }

    protected function connect()
    {
        $params = array(
            'hosts'      => array(
                $this->getParameter('host', self::DEFAULT_HOST),
            ),
        );

        $this->connection = new Client($params);
    }

    public function shutdown()
    {
        $this->connection = NULL;
    }

    public function setup()
    {
        $alias_name = $this->index_config['name'];
        $index_name = $this->getRealIndexName($alias_name);

        $this->createIndex($index_name);
        $this->switchIndexAlias($alias_name, $index_name);
    }

    protected function createIndex($index_name)
    {
        $definition_file = $this->index_config['definition_file'];
        $definition = json_decode(file_get_contents($definition_file), true);

        $mappings = $this->getMappings();
        if (count($mappings) > 0) {
            $definition['mappings'] = $mappings;
        }

        $params = array(
            "index" => $index_name,
            "body" => $definition,
        );

        $this->connection->indices()->create($params);
    }

    protected function getRealIndexName($name)
    {
        return $name . '_' . date('Y-m-d_H-i-s');
    }

    protected function switchIndexAlias($alias_name, $index_name, $delete_old_index = false)
    {
        try {
            $alias = $this->connection->indices()->getAlias(array(
                "name" => $alias_name,
            ));

            $aliased_index_names = array_keys($alias);

            foreach($aliased_index_names as $iname) {
                $this->connection->indices()->deleteAlias(array(
                    "name" => $alias_name,
                    "index" => $iname,
                ));
            }
        } catch (Missing404Exception $exception) {

        }

        $this->connection->indices()->putAlias(array(
            "name" => $alias_name,
            "index" => $index_name,
        ));

        if ($delete_old_index) {
            foreach($aliased_index_names as $iname) {
                $this->connection->indices()->delete(array(
                    "index" => $iname
                ));
            }
        }
    }

    protected function getMappings()
    {
        $mappings = array();
        foreach($this->index_config['types'] as $name => $filepath) {
            $definition = json_decode(file_get_contents($filepath), true);
            $mappings[$name] = $definition;
        }

        return $mappings;
    }

    public function getIndexName()
    {
        $index_config = $this->index_config;
        return $index_config['name'];
    }

    public function reindex($delete_old_index = false)
    {
        $alias_name = $this->index_config['name'];
        $index_name = $this->getRealIndexName($alias_name);

        //create the new index
        $this->createIndex($index_name);

        $scroll_size = 20;

        //Prepare the scan search
        $params = array(
            "index" => $alias_name,
            "scroll" => "5m",
            "size" => $scroll_size,
        );

        $results = $this->connection->search($params);
        $scroll_id = $results["_scroll_id"];
        $hits = $results["hits"]["hits"];

        do {
            foreach($hits as $hit) {
                $new_params = array(
                    "index" => $index_name,
                    "type" => $hit["_type"],
                    "id" => $hit["_id"],
                    "body" => $hit["_source"],
                );

                $this->connection->index($new_params);
            }

            // do the next scroll step
            $params = array(
                "scroll" => "5m",
                "scroll_id" => $scroll_id,
            );

            $results = $this->connection->scroll($params);
            $scroll_id = $results["_scroll_id"];
            $hits = $results["hits"]["hits"];

        } while (count($hits) > 0);

        //Switch alias to point to the new index
        $this->switchIndexAlias($alias_name, $index_name, $delete_old_index);
    }
}

