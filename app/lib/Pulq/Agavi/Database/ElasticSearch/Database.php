<?php

namespace Pulq\Agavi\Database\ElasticSearch;

use Pulq\Agavi\Database\PulqDatabase;
use Elastica;
use Elastica\Status;
use \AgaviDatabaseException;
use \AgaviDatabaseManager;

class Database extends PulqDatabase
{
    const DEFAULT_SETUP = 'Pulq\Agavi\Database\ElasticSearch\DatabaseSetup';

    const DEFAULT_PORT = 9200;

    const DEFAULT_HOST = 'localhost';

    const DEFAULT_TRANSPORT = 'Http';

    /**
     * The client used to talk to elastic search.
     *
     * @var Elastica_Client
     */
    protected $connection;

    /**
     * The elastic search index that is considered as our 'connection'
     * which stands for the resource this class works on.
     *
     * @var Elastica_Index
     */
    protected $resource;


    public function initialize(AgaviDatabaseManager $database_manager, array $parameters = array())
    {
        parent::initialize($database_manager, $parameters);
        $this->index_config = $this->getParameter('index');
    }

    protected function connect()
    {
        try
        {
            $indexName = $this->index_config['name'];

            if (! $indexName)
            {
                throw new AgaviDatabaseException("Missing required index param in current configuration.");
            }

            $this->connection = new Elastica\Client(
                array(
                    'host'      => $this->getParameter('host', self::DEFAULT_HOST),
                    'port'      => $this->getParameter('port', self::DEFAULT_PORT),
                    'transport' => $this->getParameter('transport', self::DEFAULT_TRANSPORT)
                )
            );

            $this->resource = $this->connection->getIndex($indexName);
        }
        catch (Exception $e)
        {
            throw new \AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function shutdown()
    {
        $this->connection = NULL;
        $this->resource = NULL;
    }

    public function setup()
    {
        $connection = $this->getConnection();

        $alias_name = $this->index_config['name'];
        $index_name = $alias_name . '_' . date('Y-m-d_H-i-s');

        $existing_indices = array();
        $status = new Status($connection);
        foreach ($status->getIndicesWithAlias( $alias_name ) as $aliased_index ) {
            $existing_indices[] = $aliased_index;
        }

        $definition_file = $this->index_config['definition_file'];
        $definition = json_decode(file_get_contents($definition_file), true);

        $mappings = $this->getMappings();
        if (count($mappings) > 0) {
            $definition['mappings'] = $mappings;
        }

        $connection->getIndex($index_name)->create($definition);

        $connection->getIndex($index_name)->addAlias($alias_name, true);

        foreach ($existing_indices as $existing_index) {
            $existing_index->delete();
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
}

