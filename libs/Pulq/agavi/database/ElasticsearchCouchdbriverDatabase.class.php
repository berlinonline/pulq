<?php

/**
 * Provide elasticsearch database connection handle
 *
 * @package Database
 * @author tay
 * @since 10.10.2011
 *
 */
class ElasticsearchCouchdbriverDatabase extends ElasticSearchDatabase implements IDatabaseSetupAction
{
    /**
     * method for impliciet creating of index
     *
     * @throws AgavDatabaseException
     * @deprecated use createIndexAndRiver() instead
     * @see createIndexAndRiver()
     */
    public function createIndex()
    {
        //throw new AgaviDatabaseException('Use method ' . __CLASS__ . '::createIndexAndRiver() for creating a new index');
    }


    /**
     * create a new index setup a couchdb river to feed this index
     *
     * This method does:
     *
     * <ul>
     * <li> create an new elasticsearch index using JSON file defined by parameter 'index_definition_file'
     * <li> setup couchdb river to the new index using the parameter 'river_url'
     * <li> wait for completed syncronisation between the two databases
     * <li> switch the elasticsearch alias to the new index
     * <li> delete old unused index
     * </ul>
     *
     * used parameters in our database config:
     *
     * <ul>
     * <li> index_definition_file - path to elasticsearch mapping definition as JSON
     * <li> river_script - river script (javascript usable for running inside elasticsearch)
     * <li> river_url - URL to couchdb in cluster environment (do not use localhost)
     * <li> river_settings - associative settings array for the 'index' section in elasticsearch river definition
     * <li> couchdb - name of agavi database config (source database for the river)
     * </ul>
     *
     * used parameters in couchdb client database config
     *
     * <ul>
     * <li> database - name of couchdb database
     * <li> url - URL used in our client; only used in absence of 'river_url'
     * </ul>
     *
     * The couchdb client connection is needed to read the sequence number for the syncing.
     *
     * @throws AgaviDatabaseException
     */
    public function createIndexAndRiver()
    {
        $couchdbConfigName = $this->getParameter('couchdb', 'couchdb');
        $couchDb = $this->getDatabaseManager()->getDatabase($this->getParameter('couchdb'));
        if (! $couchDb instanceof CouchDatabase)
        {
            throw new AgaviDatabaseException(
                    'Parameter "couchdb" must point to a database config using class "CouchDatabase"');
        }

        $indexParams = $this->getParameter('index');
        $esIndexName = $indexParams['name'] . date('-ymd-Hi');
        $setupDir = $indexParams['setup_dir'];
        $idxFileName = realpath($setupDir . '/' . $indexParams['definition_filename']);
        $idxFile = file_get_contents($idxFileName);
        $idxDef = json_decode($idxFile, TRUE);
        if (!is_array($idxDef) || JSON_ERROR_NONE != json_last_error())
        {
            throw new Exception('Invalid JSON: ' . $idxFileName);
        }

        if (!isset($idxDef['mappings']))
        {
            $idxDef['mappings'] = array();
        }
        $idxDef['mappings'] = array_merge($idxDef['mappings'], $this->getTypeDefinitions());

        echo "Create new elasticsearch index: '$esIndexName' …\n";
        $esIndex = $this->getConnection()
                ->getIndex($esIndexName);
        $response = $esIndex->create($idxDef);

        if ($response->hasError())
        {
            throw new AgaviDatabaseException($response->getError());
        }

        echo "Create couchdb river…\n";

        $couchDbClient = $couchDb->getConnection($couchDb);
        $dbUrl =
            parse_url(
                $this->getParameter('river_url', $couchDb->getParameter('url', ExtendedCouchDbClient::DEFAULT_URL)));

        $river =
            array(
                "type" => "couchdb",
                "couchdb" => array(
                        "host" => $dbUrl['host'],
                        "port" => $dbUrl['port'],
                        "db" => $couchDb->getParameter('database'),
                        "script" => $this->getRiverScript()
                ),
                "index" => array_merge(
                        array(
                                "bulk_size" => "1000", "bulk_timeout" => "1s"
                        ), $this->getParameter('river_settings', array()),
                        array(
                                "index" => $esIndexName
                        ))
            );

        $response = $this->getConnection()
                ->request("/_river/${esIndexName}_river/_meta", 'PUT', $river);
        if ($response->hasError())
        {
            throw new Exception($response->getError());
        }
        echo "OK\n";

        echo "Wait for river sync\n";
        for (;;)
        {
            sleep(2);
            $dbInfo = $couchDbClient->getDatabase(NULL);
            if (!is_array($dbInfo))
            {
                throw new AgaviDatabaseException("Can not get couchdb database info!");
            }
            $couchSeq = $dbInfo['committed_update_seq'];
            $response = $this->getConnection()
                    ->request("/_river/${esIndexName}_river/_seq", 'GET');
            if ($response->hasError())
            {
                throw new AgaviDatabaseException($response->getError());
            }
            $esData = $response->getData();
            if (!isset($esData['_source']['couchdb']['last_seq']))
            {
                if (isset($esData['_type']) && FALSE === $esData['exists'])
                {
                    echo "Wait for river start …\n";
                    continue;
                }
                else
                {
                    throw new AgaviDatabaseException("Can not read elasticsearch _river info!");
                }
            }
            $esSeq = $esData['_source']['couchdb']['last_seq'];
            $percent = 100 * $esSeq / $couchSeq;
            printf("\r%s %d%% %s (%d/%d) ", str_repeat('+', $percent / 4), $percent,
                str_repeat('-', (100 - $percent) / 4), $esSeq, $couchSeq);
            flush();
            if ($esSeq >= $couchSeq)
            {
                echo "\n";
                $this->actionEnable();
                $this->actionDelete();
                break;
            }
        }
    }

    protected function getRiverScript()
    {
        $indexParams = $this->getParameter('index');
        $setupDir = $indexParams['setup_dir'];
        $riverScriptParam = $this->getParameter('river_script', '');
        $scriptFilePath = realpath($setupDir . '/' . $riverScriptParam);
        if (is_readable($scriptFilePath))
        {
            $uglifyPath = str_replace('/', DIRECTORY_SEPARATOR, AgaviConfig::get('core.app_dir').'/../libs/node_modules/uglifyjs/bin/uglifyjs');

            $script = shell_exec($uglifyPath . ' -nm -nc ' . $scriptFilePath);
            return $script;
        }
        else
        {
            return $riverScriptParam;
        }
    }

    /**
     * Get the mapping description from the appropriate json files
     *
     */
    protected function getTypeDefinitions()
    {
        $idxParams = $this->getParameter('index');
        $typeNames = $idxParams['types'];
        $typeDefs = array();

        foreach ($typeNames as $typeName)
        {
            $mappingFilePath = realpath($idxParams['setup_dir'] . '/' . $typeName . '.mapping.json');
            $mappingDef = json_decode(file_get_contents($mappingFilePath), TRUE);

            if (!is_array($mappingDef) || JSON_ERROR_NONE != json_last_error())
            {
                throw new Exception('Invalid JSON in file ' . $mappingFilePath);
            }

            $typeDefs[$typeName] = $mappingDef;

        }

        return $typeDefs;
    }

    
    
    /**
     * (non-PHPdoc)
     * @see ElasticSearchDatabase::actionCreate()
     */
    public function actionCreate($tearDownFirst = FALSE)
    {
        $this->createIndexAndRiver();
    }
}
