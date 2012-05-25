<?php
/**
 * Provide elastic search database connection handle
 *
 * @author tay
 * @version $Id: ElasticSearchDatabase.class.php 1218 2012-05-20 23:20:11Z tschmitt $
 * @since 10.10.2011
 * @package Hero
 * @subpackage Agavi/Database
 */
class ElasticSearchDatabase extends AgaviDatabase
{
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
        $this->registerAutoload();

        try
        {
            $this->connection = new Elastica_Client(
                array(
                    'host'      => $this->getParameter('host', 'localhost'),
                    'port'      => $this->getParameter('port', 9200),
                    'transport' => $this->getParameter('transport', 'Http')
                )
            );
            $indexDef = $this->getParameter('index', array());
            $indexName = isset($indexDef['name']) ? $indexDef['name'] : NULL;
            $this->resource = $this->connection->getIndex($indexName);
        }
        catch (Exception $e)
        {
            throw new AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
        }

        try
        {
            $this->resource->getStatus();
        }
        catch (Elastica_Exception_Response $e)
        {
            if (FALSE !== strpos($e->getMessage(), 'IndexMissingException'))
            {
                $this->createIndex();
            }
            else
            {
                throw new AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
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
        $indexDef = $this->getParameter('index', array());
        if (! isset($indexDef['setup_class']))
        {
            $this->resource->create();
            return;
        }
        $setupClass = $indexDef['setup_class'];
        if (! class_exists($setupClass))
        {
            throw new AgaviDatabaseException("Setup class '$setupClass' can not be found.");
        }
        $indexSetup = new $setupClass($this->getName());
        if (! ($indexSetup instanceof IDatabaseSetup))
        {
            throw new AgaviDatabaseException('Setup class does not implement IDatabaseSetup: '.$setupClass);
        }
        $indexSetup->setup();
    }

    protected function registerAutoload()
    {
        $libDir = realpath(
            $this->getParameter(
                'libdir',
                AgaviConfig::get('project.libs') . DIRECTORY_SEPARATOR . 'Elastica' . DIRECTORY_SEPARATOR . 'lib'
            )
        );

        spl_autoload_register(function($class) use ($libDir)
        {
            $fileName = str_replace('_', DIRECTORY_SEPARATOR, $class . '.php');
            $filePath = $libDir . DIRECTORY_SEPARATOR . $fileName;

            if (file_exists($filePath))
            {
                require $filePath;
            }
        });
    }
}

?>
