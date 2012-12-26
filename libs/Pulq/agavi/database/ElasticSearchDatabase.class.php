<?php

/**
 * Provide elastic search database connection handle
 *
 * @author tay, tschmitt
 * @since 10.10.2011
 * @package Pulq
 * @subpackage Agavi/Database
 */
class ElasticSearchDatabase extends AgaviDatabase implements IDatabaseSetupAction
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
            $this->connection =
                new Elastica_Client(
                    array(
                        'host' => $this->getParameter('host', 'localhost'),
                        'port' => $this->getParameter('port', 9200),
                        'transport' => $this->getParameter('transport', 'Http')
                    ));
            $indexDef = $this->getParameter('index', array());
            $indexName = isset($indexDef['name']) ? $indexDef['name'] : NULL;
            $this->resource = $this->connection
                    ->getIndex($indexName);
        }
        catch (Exception $e)
        {
            throw new AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
        }
    }


    /**
     * free our resources
     * 
     * @see AgaviDatabase::shutdown()
     */
    public function shutdown()
    {
        $this->connection = NULL;
        $this->resource = NULL;
    }

    /**
     * create new search index
     * 
     * @param booelean $tearDownFirst optional flag to force creating the index from scratch
     * @throws AgaviDatabaseException
     */
    protected function createIndex($tearDownFirst = FALSE)
    {
        $indexDef = $this->getParameter('index', array());
        if (!isset($indexDef['setup_class']))
        {
            $this->resource
                ->create();
            return;
        }
        if (isset($indexDef['module']))
        {
            $this->getDatabaseManager()
                ->getContext()
                ->getController()
                ->initializeModule($indexDef['module']);
        }
        $setupClass = $indexDef['setup_class'];
        if (!class_exists($setupClass))
        {
            throw new AgaviDatabaseException("Setup class '$setupClass' can not be found.");
        }
        $indexSetup = new $setupClass();
        if (!($indexSetup instanceof IDatabaseSetup))
        {
            throw new AgaviDatabaseException('Setup class does not implement IDatabaseSetup: ' . $setupClass);
        }
        $indexSetup->setDatabase($this);
        $indexSetup->setup($tearDownFirst);
    }

    /**
     * (non-PHPdoc)
     * @see IDatabaseSetup::setup()
     */
    public function actionCreate($tearDownFirst = FALSE)
    {
        $this->createIndex($tearDownFirst);
    }

    /**
     * (non-PHPdoc)
     * @see IDatabaseSetupAction::actionDelete()
     */
    public function actionDelete()
    {
        $this->deleteUnusedIndexes();
    }


    /**
     * (non-PHPdoc)
     * @see IDatabaseSetupAction::actionEnable()
     */
    public function actionEnable()
    {
        $indexDef = $this->getParameter('index', array());
        $alias = (!$alias && isset($indexDef['name'])) ? $indexDef['name'] : NULL;

        if (!$alias)
        {
            throw new AgaviDatabaseException('No alias name defined');
        }
        $indexNames = $this->connection
                ->getStatus()
                ->getIndexNames();

        $indexNames =
            array_filter($indexNames,
                function ($idx) use ($alias)
                {
                    return preg_replace('/-\d{6}-\d{4}$/', '', $idx) == $alias;
                });


        rsort($indexNames);
        $index = $this->connection
                ->getIndex($indexNames[0]);
        $index->addAlias($alias, TRUE);
    }


    /**
     * switch alias of given elasticsearch index
     * 
     * @param Elastica_Index $index optional index; defaults to our {@see $resource} member
     * @param string $alias optional alias name; defaults to index name defined in parameters
     */
    public function switchAlias(Elastica_Index $index = NULL, $alias = NULL)
    {
        if (!$index)
        {
            $index = $this->resource;
            if (!$index)
            {
                throw new AgaviDatabaseException('No elastic search index given');
            }
        }

        if (!$alias)
        {
            $indexDef = $this->getParameter('index', array());
            $alias = (!$alias && isset($indexDef['name'])) ? $indexDef['name'] : NULL;

            if (!$alias)
            {
                throw new AgaviDatabaseException('No alias name defined');
            }
        }
        $index->addAlias($alias, TRUE);
    }


    /**
     * delete unused indexes (indexes without our alias)
     * 
     * @throws AgaviDatabaseException
     */
    public function deleteUnusedIndexes()
    {
        $indexDef = $this->getParameter('index', array());
        $alias = isset($indexDef['name']) ? $indexDef['name'] : NULL;

        if (!$alias)
        {
            throw new AgaviDatabaseException('No alias name defined');
        }

        $riverIndex = $this->connection
                ->getIndex('_river');

        $indexNames = $this->connection
                ->getStatus()
                ->getIndexNames();

        $indexNames =
            array_filter($indexNames,
                function ($idx) use ($alias)
                {
                    return preg_replace('/-\d{6}-\d{4}$/', '', $idx) == $alias;
                });

        sort($indexNames);

        foreach ($indexNames as $iname)
        {
            PulqToolkit::log(__METHOD__, "Check index '$iname' for active alias '$alias'");
            $index = $this->connection->getIndex($iname);
            if (!$index->getStatus()->hasAlias($alias))
            {
                try
                {
                    if (0 < $riverIndex->getType($iname . '_river')->count())
                    {
                        PulqToolkit::log(__METHOD__, "Delete river '${iname}_river'");
                        $this->connection->request("/_river/${iname}_river", Elastica_Request::DELETE);
                    }
                }
                catch (Elastica_Exception_Response $e)
                {
                    // No river to delete
                }
                catch (Elastica_Exception_Client $exception)
                {
                    PulqToolkit::log(__METHOD__, "Deleting corresponding _river failed: " . $exception->getMessage(),
                        'error');
                }

                try
                {
                    PulqToolkit::log(__METHOD__, "Delete index '$iname'");
                    $index->delete();
                }
                catch (Exception $exception)
                {
                    PulqToolkit::log(__METHOD__, "Deleting index failed: " . $exception->getMessage(), 'error');
                }
            }
        }
    }


    /**
     * copy old in index to new index
     *
     * @param string $fromName name of source index; should be the index alias name 
     * @param string $toName name of destination index
     * @param int $batchSize number of documents to copy at once; defaults to 1000
     */
    public function copyIndex($fromName, $toName, $batchSize = 1000)
    {
        $fromIndex = $this->connection
                ->getIndex($fromName);
        $toIndex = $this->connection
                ->getIndex($toName);

        PulqToolkit::log(__METHOD__, "Try to copy old index '$fromName' to '$toName'");
        try
        {
            $query = new Elastica_Query();
            $query->setSize($batchSize);
            for ($from = 0; TRUE; $from += $batchSize)
            {
                $query->setFrom($from);
                $result = $fromIndex->search($query);
                $hits = $result->getResults();
                if (empty($hits))
                {
                    break;
                }
                PulqToolkit::log(__METHOD__,
                    sprintf("… copy %d to %d of %d", $from, $from + count($hits), $result->getTotalHits()));
                flush();
                $batch = array();
                foreach ($hits as $item)
                {
                    /* @var $item Elastica_Result */
                    $doc = new Elastica_Document($item->getId(), $item->getSource(), $item->getType());
                    $data = $item->getData();
                    $batch[$item->getType()][] = $doc;
                }
                foreach ($batch as $type => $docs)
                {
                    $toIndex->getType($type)
                        ->addDocuments($docs);
                }
            }
            PulqToolkit::log(__METHOD__, "done.");
        }
        catch (Elastica_Exception_Response $e)
        {
            if (0 !== strpos($e->getMessage(), 'IndexMissingException'))
            {
                throw $e;
            }
        }
    }

    /**
     * 
     * Register elastica client libraries
     */
    protected function registerAutoload()
    {
        $libDir =
            realpath(
                $this->getParameter('libdir',
                        AgaviConfig::get('project.libs') . DIRECTORY_SEPARATOR . 'Elastica' . DIRECTORY_SEPARATOR
                            . 'lib'));

        spl_autoload_register(
            function ($class) use ($libDir)
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
