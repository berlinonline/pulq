<?php

/**
 *
 *
 * @author tay
 * @since 26.11.2012
 *
 */
class GeoCache implements IDatabaseSetup
{
    const DEFAULT_MAX_AGE = 2592000;

    const ES_TYPE = 'cache';

    /**
     * try to get response from cache
     *
     * @param GeoRequest $req
     * @param int $max_age maximum age of cached response in seconds
     * @return GeoResponse
     */
    public function fetch(GeoRequest $req, $max_age = self::DEFAULT_MAX_AGE)
    {
        if ($max_age <= 0)
        {
            return NULL;
        }

        $elastica = $this->getEsIndex();
        try
        {
            $cache = $elastica->getType(self::ES_TYPE)
                    ->getDocument($req->hash());
            $data = $cache->getData();
            if (!isset($data['response']['meta']['timestamp'])
                || ($data['response']['meta']['timestamp'] / 1000) < (time() - $max_age))
            {
                return NULL;
            }

            $response = GeoResponse::getInstanceForResult($data['response']);
            return $response;
        }
        catch (Elastica_Exception_NotFound $e)
        {
            return NULL;
        }
    }


    /**
     * put a request/response tupel to the cache
     *
     * @param GeoRequest $req
     * @param GeoResponse $resp
     */
    public function put(GeoRequest $req, GeoResponse $resp)
    {
        $data = array(
                'request' => $req->toArray(), 'response' => $resp->toArray()
            );
        $doc = new Elastica_Document($req->hash(), $data);

        $elastica = $this->getEsIndex();
        $cache = $elastica->getType(self::ES_TYPE)
                ->addDocument($doc);
    }


    /**
     * (non-PHPdoc)
     * @see IDatabaseSetup::setup($tearDownFirst)
     */
    public function setup($tearDownFirst = FALSE)
    {
        $db = AgaviContext::getInstance()->getDatabaseManager()
                ->getDatabase('geocache');

        $elastica = $db->getResource();
        $esIndexName = $elastica->getName() . date('-ymd-Hi');
        $esIndex = $this->createIndex($elastica->getClient(), $esIndexName);

        $esType = $esIndex->getType(self::ES_TYPE);
        $alias = $elastica->getName();
        $esIndex->addAlias($alias, TRUE);

        $indexNames = $db->getConnection()
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
            echo "Check index '$iname' for active alias '$alias'\n";
            $index = $db->getConnection()
                    ->getIndex($iname);
            if (!$index->getStatus()
                ->hasAlias($alias))
            {
                try
                {
                    echo "Delete index '$iname'\n";
                    $index->delete();
                }
                catch (Exception $exception)
                {
                    echo "Deleting index failed: " . $exception->getMessage() . PHP_EOL;
                }
            }
        }

        return AgaviView::NONE;
    }


    /**
     *
     *
     * @param Elastica_Client $es
     * @param unknown_type $name
     * @return Elastica_Index
     */
    public function createIndex(Elastica_Client $es, $name)
    {
        $elasticaIndex = $es->getIndex($name);

        // Create the index new
        $elasticaIndex->create(
                array(
                    "settings" => array(
                        "number_of_shards" => 1,
                        "number_of_replicas" => 1,
                        "analysis" => array(
                            "char_filter" => array(
                                "boid_mapping" => array(
                                    "type" => "mapping",
                                    "mappings" => array(
                                        "ä=>ae", "ü=>ue", "ö=>oe", "Ä=>ae", "Ü=>ue", "Ö=>oe", "ß=>ss"
                                    )
                                )
                            ),
                            "filter" => array(
                                "snowball_german2" => array(
                                    "type" => "snowball", "language" => "German2"
                                )
                            ),
                            "analyzer" => array(
                                "german2" => array(
                                    "type" => "custom",
                                    "tokenizer" => "standard",
                                    "filter" => array(
                                        "icu_normalizer", "icu_folding", "lowercase", "snowball_german2"
                                    )
                                ),
                                "azSortLower" => array(
                                    "type" => "custom",
                                    "tokenizer" => "keyword",
                                    "filter" => array(
                                        "asciifolding", "lowercase"
                                    )
                                ),
                                "boid" => array(
                                    "type" => "custom",
                                    "tokenizer" => "keyword",
                                    "filter" => array(
                                        "asciifolding", "lowercase"
                                    ),
                                    "char_filter" => array(
                                        "boid_mapping"
                                    )
                                )
                            )
                        )
                    ),
                    "mappings" => array(
                        self::ES_TYPE => array(
                            "dynamic" => false,
                            "_all" => array(
                                "enabled" => true, "analyzer" => "german2"
                            ),
                            "_source" => array(
                                "enabled" => true
                            ),
                            "_timestamp" => array(
                                "enabled" => true, "path" => "response.meta.date"
                            ),
                            "properties" => array(
                                "request" => array(
                                    "type" => "object",
                                    "properties" => array(
                                        "query" => array(
                                            "type" => "string", "index" => "not_analyzed"
                                        ),
                                        "country" => array(
                                            "type" => "string", "index" => "not_analyzed"
                                        ),
                                        "city" => array(
                                            "type" => "string", "index" => "not_analyzed"
                                        ),
                                        "postal" => array(
                                            "type" => "string", "index" => "not_analyzed"
                                        ),
                                        "street" => array(
                                            "type" => "string", "index" => "not_analyzed"
                                        ),
                                        "house" => array(
                                            "type" => "string", "index" => "not_analyzed"
                                        )
                                    )
                                ),
                                "response" => array(
                                    "type" => "object",
                                    "properties" => array(
                                        "location" => array(
                                            "type" => "object",
                                            "properties" => array(
                                                "wgs84" => array(
                                                    "type" => "geo_point"
                                                )
                                            )
                                        ),
                                        "meta" => array(
                                            "type" => "object",
                                            "properties" => array(
                                                "source" => array(
                                                    "type" => "string", "index" => "not_analyzed"
                                                ),
                                                "date" => array(
                                                    "type" => "date"
                                                ),
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                ));
        return $elasticaIndex;
    }


    /**
     * @return Elastica_Index
     */
    protected function getEsIndex()
    {
        /* @var $db ElasticSearchDatabase */
        $db = AgaviContext::getInstance()->getDatabaseManager()
                ->getDatabase('geocache');

        /* @var $elastica Elastica_Index */
        return $db->getResource();
    }
}
