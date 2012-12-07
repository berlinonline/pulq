<?php

/**
 *
 *
 * @author tay
 * @since 09.11.2012
 *
 */
class HaKoDeSetup implements IDatabaseSetup
{
   /**
     *
     * @var array
     */
    protected $streets = array();
    /**
     *
     * @var array
     */
    protected $potplz = array();

    /**
     * (non-PHPdoc)
     * @see IDatabaseSetup::setup($tearDownFirst)
     */
    public function setup($tearDownFirst = FALSE)
    {
        /* @var $db ElasticSearchDatabase */
        $db = AgaviContext::getInstance()->getDatabaseManager()
                ->getDatabase('HaKoDe');

        $filename = $db->getParameter('hakode_file');
        $fd = fopen($filename, "r");
        if (!$fd)
        {
            throw new AgaviDatabaseException('Can not open defined in database config parameter "hakode_file": ' . $filename);
        }

        $gp = new gPoint('GRS 1980');

        /* @var $elastica Elastica_Index */
        $elastica = $db->getResource();

        $esIndexName = $elastica->getName() . date('-ymd-Hi');
        $esIndex = $this->createIndex($elastica->getClient(), $esIndexName);

        $esType = $esIndex->getType('house');

        $startTime = time();
        echo "Start importing house data at " . strftime('%c') . "\n";

        $count = 0;
        $docs = array();
        while (FALSE !== ($inp = fgets($fd, 32768)))
        {
            $inp = mb_convert_encoding($inp, 'UTF-8', 'ISO-8859-1');
            $line = str_getcsv($inp, ';');
            if (count($line) != 21)
            {
                throw new AgaviDatabaseException('File format error: ' . $filename);
            }

            $id = $line[1];
            $hnr = $line[9];
            $adz = $line[10];
            $z_easting = $line[11];
            $northing = $line[12];
            $stn = $line[13];
            $plz = $line[14];
            $onm = $line[15];
            $pot = $line[17];

            $utm_zone = substr($z_easting, 0, 2);
            $easting = floatval(strtr(substr($z_easting, 2), ',', '.'));
            $northing = floatval(strtr($northing, ',', '.'));

            $gp->setUTM($easting, $northing, $utm_zone . 'U');
            $gp->convertTMtoLL();

            $data =
                array(
                    'id' => $id,
                    'plz' => $plz,
                    'onm' => $onm,
                    'pot' => $pot,
                    'stn' => $stn,
                    'hnr' => $hnr,
                    'adz' => $adz,
                    'utm' => array(
                        'easting' => $easting, 'northing' => $northing, 'zone' => $utm_zone . 'U'
                    ),
                    'etrs89' => array(
                        'lat' => round($gp->Lat(), 5), 'lon' => round($gp->Long(), 5)
                    )
                );

            if (empty($stn) || empty($plz))
            {
                continue;
            }

            $this->addStreet($data);

            $docs[] = new Elastica_Document($id, $data);
            if (1000 <= count($docs))
            {
                $esType->addDocuments($docs);
                $count += count($docs);
                echo "\rImported $count Elapsed seconds: " . (time() - $startTime);
                flush();
                $docs = array();
            }
        }

        if (!empty($docs))
        {
            $esType->addDocuments($docs);
            $count += count($docs);
        }
        printf("\rFinish importing houses: %d after %d seconds at %s\n", $count, (time() - $startTime), strftime('%c'));

        $this->flushStreets($esIndex);

        $esIndex->addAlias($elastica->getName(), TRUE);

        return AgaviView::NONE;
    }

    /**
     *
     *
     * @param array $house
     */
    protected function addStreet(array $house)
    {
        $key = sprintf('street:%s:%s', $house['stn'], $house['plz']);
        if (array_key_exists($key, $this->streets))
        {
            $old = &$this->streets[$key];

            $old['sum']['lat'] += $house['etrs89']['lat'];
            $old['sum']['lon'] += $house['etrs89']['lon'];
            $old['count']++;

            if ($old['north']['lat'] < $house['etrs89']['lat'])
            {
                $old['north'] = $house['etrs89'];
            }
            if ($old['south']['lat'] > $house['etrs89']['lat'])
            {
                $old['south'] = $house['etrs89'];
            }
            if ($old['west']['lon'] > $house['etrs89']['lon'])
            {
                $old['west'] = $house['etrs89'];
            }
            if ($old['east']['lon'] < $house['etrs89']['lon'])
            {
                $old['east'] = $house['etrs89'];
            }
        }
        else
        {
            $street =
                array(
                    'stn' => $house['stn'],
                    'plz' => $house['plz'],
                    'pot' => $house['pot'],
                    'onm' => $house['onm'],
                    'north' => $house['etrs89'],
                    'south' => $house['etrs89'],
                    'west' => $house['etrs89'],
                    'east' => $house['etrs89'],
                    'sum' => $house['etrs89'],
                    'count' => 1
                );
            $this->streets[$key] = $street;
        }
    }


    /**
     *
     *
     * @param array $house
     */
    protected function addPotPlz($type, $key, array $street)
    {
        if (isset($this->potplz[$type][$key]))
        {
            $old = &$this->potplz[$type][$key];

            $old['sum']['lat'] += $street['sum']['lat'];
            $old['sum']['lon'] += $street['sum']['lon'];
            $old['count'] += $street['count'];

            if ($old['north']['lat'] < $street['north']['lat'])
            {
                $old['north'] = $street['north'];
            }
            if ($old['south']['lat'] > $street['south']['lat'])
            {
                $old['south'] = $street['south'];
            }
            if ($old['west']['lon'] > $street['west']['lon'])
            {
                $old['west'] = $street['west'];
            }
            if ($old['east']['lon'] < $street['east']['lon'])
            {
                $old['east'] = $street['east'];
            }
        }
        else
        {
            $pot =
                array(
                    'plz' => $street['plz'],
                    'pot' => $street['pot'],
                    'onm' => $street['onm'],
                    'north' => $street['north'],
                    'south' => $street['south'],
                    'west' => $street['west'],
                    'east' => $street['east'],
                    'sum' => $street['sum'],
                    'count' => $street['count'],
                );
            $this->potplz[$type][$key] = $pot;
        }
    }

    /**
     *
     *
     * @param Elastica_Index $esIndex
     */
    protected function flushStreets(Elastica_Index $esIndex)
    {
        $startTime = time();
        echo "\nStart importing streets data at " . strftime('%c') . "\n";

        $esType = $esIndex->getType('street');

        $gp = new gPoint('GRS 1980');

        $count = 0;
        $docs = array();
        foreach ($this->streets as $id => $street)
        {
            if (!empty($street['pot']))
            {
                $key = sprintf('pot:%s', $street['pot']);
                $this->addPotPlz('pot', $key, $street);
            }
            if (!empty($street['plz']))
            {
                $key = sprintf('plz:%s', $street['plz']);
                $this->addPotPlz('plz', $key, $street);
            }

            $street['northwest']['lat'] = $street['north']['lat'];
            $street['northwest']['lon'] = $street['west']['lon'];
            $street['southeast']['lat'] = $street['south']['lat'];
            $street['southeast']['lon'] = $street['east']['lon'];

            $street['avg']['lat'] = round($street['sum']['lat'] / $street['count'], 5);
            $street['avg']['lon'] = round($street['sum']['lon'] / $street['count'], 5);

            unset($street['sum'], $street['count']);

            $docs[] = new Elastica_Document($id, $street);
            if (count($docs) > 1000)
            {
                $count += count($docs);
                $esType->addDocuments($docs);
                echo "\rImported $count Elapsed seconds: " . (time() - $startTime);
                $docs = array();
            }
        }
        if (!empty($docs))
        {
            $esType->addDocuments($docs);
            $count += count($docs);
        }
        printf("\rFinish importing streets: %d after %d seconds at %s\n", $count, (time() - $startTime), strftime('%c'));

        $this->flushPotPlz($esIndex);
    }

    /**
     *
     *
     * @param Elastica_Index $esIndex
     */
    protected function flushPotPlz(Elastica_Index $esIndex)
    {
        $gp = new gPoint('GRS 1980');
        foreach ($this->potplz as $type => $list)
        {
            $startTime = time();
            echo "\nStart importing $type data at " . strftime('%c') . "\n";

            $esType = $esIndex->getType($type);

            $count = 0;
            $docs = array();
            foreach ($list as $id => $pot)
            {
                $pot['northwest']['lat'] = $pot['north']['lat'];
                $pot['northwest']['lon'] = $pot['west']['lon'];
                $pot['southeast']['lat'] = $pot['south']['lat'];
                $pot['southeast']['lon'] = $pot['east']['lon'];

                $pot['avg']['lat'] = round($pot['sum']['lat'] / $pot['count'], 5);
                $pot['avg']['lon'] = round($pot['sum']['lon'] / $pot['count'], 5);

                unset($pot['sum'], $pot['count']);

                $docs[] = new Elastica_Document($id, $pot);
                if (count($docs) > 1000)
                {
                    $count += count($docs);
                    $esType->addDocuments($docs);
                    echo "\rImported $count Elapsed seconds: " . (time() - $startTime);
                    $docs = array();
                }
            }
            if (!empty($docs))
            {
                $esType->addDocuments($docs);
                $count += count($docs);
            }
            printf("\rFinish importing $type: %d after %d seconds at %s\n", $count, (time() - $startTime),
                strftime('%c'));
        }
    }

    /**
     * @return Elastica_Index newly created index
     */
    protected function createIndex(Elastica_Client $es, $name)
    {
        $elasticaIndex = $es->getIndex($name);

        // Create the index new
        $elasticaIndex->create(
                array(
                    "settings" => array(
                        "number_of_shards" => 2,
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
                        "house" => array(
                            "dynamic" => false,
                            "_all" => array(
                                "enabled" => true, "analyzer" => "german2"
                            ),
                            "_source" => array(
                                "enabled" => true
                            ),
                            "properties" => array(
                                "plz" => array(
                                    "type" => "string", "index" => "not_analyzed"
                                ),
                                "onm" => array(
                                    "type" => "string", "analyzer" => "german2", "store" => "yes"
                                ),
                                "pot" => array(
                                    "type" => "multi_field",
                                    "fields" => array(
                                        "pot" => array(
                                            "type" => "string", "analyzer" => "german2", "store" => "yes"
                                        ),
                                        "sort" => array(
                                            "type" => "string", "analyser" => "azSortLower", "include_in_all" => false
                                        )
                                    )
                                ),
                                "stn" => array(
                                    "type" => "multi_field",
                                    "fields" => array(
                                        "stn" => array(
                                            "type" => "string", "analyzer" => "german2", "store" => "yes"
                                        ),
                                        "sort" => array(
                                            "type" => "string", "analyser" => "azSortLower", "include_in_all" => false
                                        )
                                    )
                                ),
                                "hnr" => array(
                                    "type" => "string", "index" => "not_analyzed"
                                ),
                                "adz" => array(
                                    "type" => "string", "index" => "not_analyzed"
                                ),
                                "etrs89" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "north" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "east" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "south" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "west" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "northwest" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "southeast" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "avg" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                )
                            )
                        ),
                        "street" => array(
                            "dynamic" => false,
                            "_all" => array(
                                "enabled" => true, "analyzer" => "german2"
                            ),
                            "_source" => array(
                                "enabled" => true
                            ),
                            "properties" => array(
                                "plz" => array(
                                    "type" => "string", "index" => "not_analyzed"
                                ),
                                "onm" => array(
                                    "type" => "string", "analyzer" => "german2", "store" => "yes"
                                ),
                                "pot" => array(
                                    "type" => "multi_field",
                                    "fields" => array(
                                        "pot" => array(
                                            "type" => "string", "analyzer" => "german2", "store" => "yes"
                                        ),
                                        "sort" => array(
                                            "type" => "string", "analyser" => "azSortLower", "include_in_all" => false
                                        )
                                    )
                                ),
                                "stn" => array(
                                    "type" => "multi_field",
                                    "fields" => array(
                                        "stn" => array(
                                            "type" => "string", "analyzer" => "german2", "store" => "yes", "boost" => 5
                                        ),
                                        "sort" => array(
                                            "type" => "string", "analyser" => "azSortLower", "include_in_all" => false
                                        )
                                    )
                                ),
                                "etrs89" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "north" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "east" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "south" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "west" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "northwest" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "southeast" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "avg" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                )
                            )
                        ),
                        "pot" => array(
                            "dynamic" => false,
                            "_all" => array(
                                "enabled" => true, "analyzer" => "german2"
                            ),
                            "_source" => array(
                                "enabled" => true
                            ),
                            "properties" => array(
                                "plz" => array(
                                    "type" => "string", "index" => "not_analyzed"
                                ),
                                "onm" => array(
                                    "type" => "string", "analyzer" => "german2", "store" => "yes"
                                ),
                                "pot" => array(
                                    "type" => "multi_field",
                                    "fields" => array(
                                        "pot" => array(
                                            "type" => "string", "analyzer" => "german2", "store" => "yes", "boost" => 5
                                        ),
                                        "sort" => array(
                                            "type" => "string", "analyser" => "azSortLower", "include_in_all" => false
                                        )
                                    )
                                ),
                                "etrs89" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "north" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "east" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "south" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "west" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "northwest" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "southeast" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "avg" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                )
                            )
                        ),
                        "plz" => array(
                            "dynamic" => false,
                            "_all" => array(
                                "enabled" => true, "analyzer" => "german2"
                            ),
                            "_source" => array(
                                "enabled" => true
                            ),
                            "properties" => array(
                                "plz" => array(
                                    "type" => "string", "index" => "not_analyzed", "boost" => 5
                                ),
                                "onm" => array(
                                    "type" => "string", "analyzer" => "german2", "store" => "yes"
                                ),
                                "pot" => array(
                                    "type" => "multi_field",
                                    "fields" => array(
                                        "pot" => array(
                                            "type" => "string", "analyzer" => "german2", "store" => "yes", "boost" => 5
                                        ),
                                        "sort" => array(
                                            "type" => "string", "analyser" => "azSortLower", "include_in_all" => false
                                        )
                                    )
                                ),
                                "etrs89" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "north" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "east" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "south" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "west" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "northwest" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "southeast" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                ),
                                "avg" => array(
                                    "type" => "geo_point", "include_in_all" => false
                                )
                            )
                        )
                    )
                ));
        return $elasticaIndex;
    }
}
