<?php

namespace Pulq\Agavi\Database\ElasticSearch;

use Pulq\Agavi\Database\CouchDb;

/**
 * Provide elasticsearch database connection handle
 *
 * @package Database
 * @author tay
 * @since 10.10.2011
 *
 */
class RiverSetupDatabase extends Database
{
    protected $outputCallback;

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
     * @throws \AgaviDatabaseException
     */
    public function createIndexAndRiver()
    {
        $couchdbConfig = $this->getParameter('couchdb');
        $couchDb = new CouchDb\Database($couchdbConfig);
        if (! $couchDb instanceof CouchDb\Database)
        {
            throw new \AgaviDatabaseException(
                    'Parameter "couchdb" must point to a database config using class "CouchDb\Database"');
        }

        $indexParams = $this->getParameter('index');
        $esIndexName = $indexParams['name'] . date('-ymd-Hi');
        $setupDir = $indexParams['setup_dir'];
        $idxFileName = realpath($setupDir . '/' . $indexParams['definition_filename']);
        $idxFile = file_get_contents($idxFileName);
        $idxDef = json_decode($idxFile, TRUE);

        if (!is_array($idxDef) || JSON_ERROR_NONE != json_last_error())
        {
            throw new \Exception('Invalid JSON: ' . $idxFileName);
        }

        if (!isset($idxDef['mappings']))
        {
            $idxDef['mappings'] = array();
        }
        $idxDef['mappings'] = array_merge($idxDef['mappings'], $this->getTypeDefinitions());

        $this->log("Create new elasticsearch index: '$esIndexName' …".PHP_EOL);
        $esIndex = $this->getConnection()
                ->getIndex($esIndexName);
        $response = $esIndex->create($idxDef);

        if ($response->hasError())
        {
            throw new \AgaviDatabaseException($response->getError());
        }

        $this->log("Create couchdb river…".PHP_EOL);

        $couchDbClient = $couchDb->getConnection($couchDb);
        $dbUrl =
            parse_url(
                $couchDb->getParameter('url', CouchDb\Client::DEFAULT_URL));

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
            throw new \Exception($response->getError());
        }
        $this->log("OK".PHP_EOL);

        $this->log("Wait for river sync".PHP_EOL);
        for (;;)
        {
            sleep(2);
            $dbInfo = $couchDbClient->getDatabase(NULL);
            if (!is_array($dbInfo))
            {
                throw new \AgaviDatabaseException("Can not get couchdb database info!");
            }
            $couchSeq = $dbInfo['committed_update_seq'];
            $response = $this->getConnection()
                    ->request("/_river/${esIndexName}_river/_seq", 'GET');
            if ($response->hasError())
            {
                throw new \AgaviDatabaseException($response->getError());
            }
            $esData = $response->getData();
            if (!isset($esData['_source']['couchdb']['last_seq']))
            {
                if (isset($esData['_type']) && FALSE === $esData['exists'])
                {
                    $this->log("Wait for river start …\n");
                    continue;
                }
                else
                {
                    throw new \AgaviDatabaseException("Can not read elasticsearch _river info!");
                }
            }
            $esSeq = $esData['_source']['couchdb']['last_seq'];
            $percent = 100 * $esSeq / $couchSeq;
            $this->log(sprintf("\r%s %d%% %s (%d/%d) ", str_repeat('+', $percent / 4), $percent,
                str_repeat('-', (100 - $percent) / 4), $esSeq, $couchSeq));
            if ($esSeq >= $couchSeq)
            {
                $this->log(PHP_EOL);
                $this->switchIndex();
                $this->deleteIndex();
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
            $uglifyPath = str_replace('/', DIRECTORY_SEPARATOR, \AgaviConfig::get('core.app_dir').'/../node_modules/uglify-js/bin/uglifyjs');

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
                throw new \Exception('Invalid JSON in file ' . $mappingFilePath);
            }

            $typeDefs[$typeName] = $mappingDef;

        }

        return $typeDefs;
    }

    /**
     * Delete oldest unused index (index without alias)
     *
     * @throws \Exception
     */
    public function deleteIndex()
    {
        $idxParams = $this->getParameter('index');
        $alias = $idxParams['name'];
        $indexNames = $this->getConnection()
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
            $this->log("Check index '$iname' for active alias '$alias'".PHP_EOL);
            $index = $this->getConnection()->getIndex($iname);
            if (!$index->getStatus()->hasAlias($alias))
            {
                try
                {
                    $this->log("Delete river '${iname}_river'".PHP_EOL);
                    $index->getClient()->request("/_river/${iname}_river", "DELETE");
                }
                catch (\Exception $exception)
                {
                    $this->log("Deleting corresponding _river failed: " . $exception->getMessage() . PHP_EOL);
                }

                try
                {
                    $this->log("Delete index '$iname'".PHP_EOL);
                    $index->delete();
                }
                catch (\Exception $exception)
                {
                    $this->log("Deleting index failed: " . $exception->getMessage() . PHP_EOL);
                }

                break; //only delete the one index. usually there should only be one to delete.
            }
        }
    }


    /**
     * Switch alias to newest index
     *
     * @param \AgaviRequestDataHolder $rd
     * @throws \AgaviDatabaseException
     */
    public function switchIndex()
    {
        $idxParams = $this->getParameter('index');
        $alias = $idxParams['name'];
        $indexNames = $this->getConnection()
                ->getStatus()
                ->getIndexNames();

        $indexNames =
            array_filter($indexNames,
                function ($idx) use ($alias)
                {
                    return preg_replace('/-\d{6}-\d{4}$/', '', $idx) == $alias;
                });

        rsort($indexNames);

        if (empty($indexNames))
        {
            throw new \Exception('No index found to switch to.');
        }

        $this->log("Available indexes: " . join(', ', $indexNames) . PHP_EOL);

        $idxName = $indexNames[0];
        $this->log("Switch alias '$alias' to index '$idxName'".PHP_EOL);

        $response = $this->getConnection()
                ->getIndex($idxName)
                ->addAlias($alias, TRUE);
        if ($response->hasError())
        {
            throw new \AgaviDatabaseException($response->getError());
        }
        $this->log("OK".PHP_EOL);
    }

    public function setOutputCallback($callback)
    {
        $this->outputCallback = $callback;
    }

    protected function log($string)
    {
        $callback = $this->outputCallback;
        if (is_callable($callback))
        {
            $callback($string);
        }
    }
}

