<?php

/**
 *
 *
 * @author tay
 * @since 26.11.2012
 *
 */
class GeoCache
{
    const ES_TYPE = 'cache';

    /**
     * try to get response from cache
     *
     * @param GeoRequest $req
     * @return GeoResponse
     */
    public function fetch(GeoRequest $req)
    {
        $elastica = $this->getEsIndex();
        try
        {
            $cache = $elastica->getType(ES_TYPE)
                    ->getDocument($req->hash());
            $data = $cache->getData();
            /* @todo Remove debug code GeoCache.class.php from 04.12.2012 */
            $__logger=AgaviContext::getInstance()->getLoggerManager();
            $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__,AgaviILogger::DEBUG);
            $__logger->log(print_r($data,1),AgaviILogger::DEBUG);

            $response = GeoResponse::getInstanceForResult($data);
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
        $data =
            array(
                'id' => $req->hash(), 'request' => $req->toArray(), 'response' => $resp->toArray()
            );
        $doc = new Elastica_Document($req->hash(), $data);

        $elastica = $this->getEsIndex();
        $cache = $elastica->getType(ES_TYPE)
                ->addDocument($doc);
    }


    /**
     * (non-PHPdoc)
     * @see IDatabaseSetup::setup($tearDownFirst)
     */
    public function setup($tearDownFirst = FALSE)
    {
        $elastica = $this->getEsIndex();
        $esIndexName = $elastica->getName() . date('-ymd-Hi');
        $esIndex = $this->createIndex($elastica->getClient(), $esIndexName);

        $esType = $esIndex->getType(ES_TYPE);
        $esIndex->addAlias($elastica->getName(), TRUE);

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
                        ES_TYPE => array(
                            "dynamic" => false,
                            "_all" => array(
                                "enabled" => true, "analyzer" => "german2"
                            ),
                            "_source" => array(
                                "enabled" => true
                            ),
                            "properties" => array(
                                "id" => array(
                                    "type" => "string", "index" => "not_analyzed"
                                ),
                                "_timestamp" => array(
                                    "enabled" => true
                                ),
                                "request" => array(
                                    "type" => "multifield",
                                    "fields" => array(
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
                                    "type" => "multifield",
                                    "fields" => array(
                                        "r_source" => array(
                                            "type" => "string", "index" => "not_analyzed"
                                        ),
                                        "r_formatted" => array(
                                            "type" => "string", "index" => "not_analyzed"
                                        ),
                                        "r_location" => array(
                                            "type" => "geo_point"
                                        ),
                                        "r_bbox" => array(
                                            "type" => "envelope"
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
