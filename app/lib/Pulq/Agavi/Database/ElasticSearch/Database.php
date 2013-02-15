<?php

namespace Pulq\Agavi\Database\ElasticSearch;

use Pulq\Agavi\Database\IDatabaseSetup;
use Elastica;

/**
 * Provide elastic search database connection handle
 *
 * @author tay
 */
class Database extends \AgaviDatabase
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

    protected function connect()
    {
        try
        {
            $indexName = $this->getParameter('index');
            if (! $indexName)
            {
                throw new \AgaviDatabaseException("Missing required index param in current configuration.");
            }

            if (! $this->hasParameter('mapping_dir'))
            {
                throw new \AgaviDatabaseException("Missing required mapping_dir param in current configuration.");
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

        try
        {
            $this->resource->getStatus();
        }
        catch (Elastica\Exception\ResponseException $e)
        {
            if (FALSE !== strpos($e->getMessage(), 'IndexMissingException'))
            {
                $this->createIndex();
            }
            else
            {
                throw new \AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    public function shutdown()
    {
        $this->connection = NULL;
        $this->resource = NULL;
    }

    protected function createIndex()
    {
        $indexName = $this->getParameter('index');
        if (! $this->getParameter('setup', FALSE))
        {
            $this->resource->create();
            return;
        }

        $setupImplementor = $this->getParameter('setup_class', self::DEFAULT_SETUP);
        if (! class_exists($setupImplementor))
        {
            throw new \AgaviDatabaseException(
                "Setup class '$setupImplementor' can not be found."
            );
        }

        $setup = new $setupImplementor();
        if (! ($setup instanceof IDatabaseSetup))
        {
            throw new \AgaviDatabaseException(
                "Setup class does not implement IDatabaseSetup: $setupImplementor"
            );
        }

        $setup->execute($this);
    }
}
