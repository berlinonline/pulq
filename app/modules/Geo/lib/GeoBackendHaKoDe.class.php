<?php

/**
 *
 *
 * @author tay
 * @since 20.04.2012
 *
 */
class GeoBackendHaKoDe extends GeoBackendBase
{
    const BACKEND = 'hakode';

    protected $data = NULL;

    /**
     * (non-PHPdoc)
     * @see GeoBackendBase::query()
     */
    public function query(GeoRequest $request)
    {
        $this->data = NULL;

        $db = AgaviContext::getInstance()->getDatabaseManager()
                ->getDatabase('HaKoDe');
        $esIndex = $db->getResource();

        $boolQuery = new Elastica_Query_Bool();
        $boolFilter = new Elastica_Filter_Bool();
        $type = NULL;

        if ($request->has('query'))
        {
            $where = mb_strtolower($request->get('query'), 'UTF-8');
            $search = new Elastica_Query_Text();
            $search->setFieldQuery('_all', preg_replace('/str\.?\b/i', 'straße', $where));
            $search->setFieldParam('_all', 'fuzziness', 0.9);
            $search->setFieldParam('_all', 'operator', 'and');
            $boolQuery->addMust($search);
            $type = 'house';
        }

        if ($request->has('street'))
        {
            $where = mb_strtolower($request->get('street'), 'UTF-8');
            $search = new Elastica_Query_Text();
            $search->setFieldQuery('stn', preg_replace('/str(asse|\.)?\b/i', 'straße', $where));
            $search->setFieldParam('stn', 'fuzziness', 0.9);
            $search->setFieldParam('stn', 'operator', 'and');
            $boolQuery->addMust($search);
            if (empty($type))
            {
                $type = 'street';
            }
        }

        if ($request->has('house'))
        {
            $where = mb_strtolower($request->get('house'), 'UTF-8');
            if (preg_match('/(\d+)\s*(\w*)/', $where, $m))
            {
                $boolFilter->addMust(
                        new Elastica_Filter_Term(
                            array(
                                'hnr' => $m[1]
                            )));
                if (!empty($m[2]))
                {
                    $boolFilter->addMust(
                            new Elastica_Filter_Term(
                                array(
                                    'adz' => $m[2]
                                )));
                }
            }
            if (empty($type))
            {
                $type = 'house';
            }
        }

        if ($request->has('postal'))
        {
            $where = mb_strtolower($request->get('postal'), 'UTF-8');
            $boolFilter->addMust(new Elastica_Filter_Term(array(
                    'plz' => $where
                )));
            if (empty($type))
            {
                $type = 'plz';
            }
        }

        if ($request->has('district'))
        {
            $where = mb_strtolower($request->get('pot'), 'UTF-8');
            $boolFilter->addMust(new Elastica_Filter_Term(array(
                    'pot' => $where
                )));
            if (empty($type))
            {
                $type = 'pot';
            }
        }

        $boolQuery->addMust(new Elastica_Query_Term(array(
                '_type' => $type
            )));

        $query = new Elastica_Query($boolQuery);
        $query->setMinScore(0.99);
        $query->setSize(1);
        $bfArray = $boolFilter->toArray();
        if (!empty($bfArray['bool']))
        {
            $query->setFilter($boolFilter);
        }

        $list = array();
        try
        {
            $__logger = AgaviContext::getInstance()->getLoggerManager();
            $__logger->log(__METHOD__ . ":" . __LINE__ . " : " . __FILE__, AgaviILogger::DEBUG);
            $__logger->log(json_encode($query->toArray()), AgaviILogger::DEBUG);

            $result = $esIndex->search($query);
            foreach ($result as $idx => $hit)
            {
                $list[] = $hit->getHit();
            }
            $this->data =
                array(
                    'state' => 'ok', 'total' => $result->getTotalHits(), 'list' => $list
                );
        }
        catch (Elastica_Exception_Response $e)
        {
            $__logger = AgaviContext::getInstance()->getLoggerManager();
            $__logger->log(__METHOD__ . ":" . __LINE__ . " : " . __FILE__, AgaviILogger::ERROR);
            $__logger->log($e->getMessage(), AgaviILogger::ERROR);
            $__logger->log(json_encode($query->toArray()), AgaviILogger::ERROR);
            throw new GeoException('Elasticsearch query failed!', GeoException::INVALID_BACKEND_RESPONSE, $e);
        }
    }

    /**
     * (non-PHPdoc)
     * @see GeoBackendBase::fillResponse()
     */
    public function fillResponse(GeoResponse $response)
    {
        $itemOne = $this->data['list'][0]['_source'];

        $response->setValue('meta.source', self::BACKEND);
        $response->setValue('meta.timestamp', time() * 1000);
        $response->setValue('meta.date', date(DATE_ISO8601));
        $response->setValue('meta.copyright', 'Hauskoordinaten Deutschland');
        $response->setValue('address.country', 'Deutschland');
        $response->setValue('address.state', $itemOne['onm']);
        $response->setValue('address.municipality', $itemOne['onm']);
        $response->setValue('address.urban-subdivision', $itemOne['pot']);
        $response->setValue('address.district', $this->getDistrict($itemOne['onm'], $itemOne['pot']));
        $response->setValue('address.administrative-district',
                $this->getAdministrativeDistrict($itemOne['onm'], $itemOne['pot']));
        $response->setValue('address.postal-code', $itemOne['plz']);
        $response->setValue('location.accuracy', GeoResponse::ACCURACY_CRAP);

        switch ($this->data['list'][0]['_type'])
        {
        case 'pot':
            $response->setValue('address.formatted',
                    sprintf('%s %s', $itemOne['plz'], $response->getValue('address.municipality')));
            $response->setValue('location.wgs84',
                    $response->buildCoordinates($itemOne['avg']['lon'], $itemOne['avg']['lat']));
            $response->setValue('location.bbox',
                    $response->buildBoundingBox($itemOne['northwest']['lon'], $itemOne['northwest']['lat'],
                            $itemOne['southeast']['lon'], $itemOne['southeast']['lat']));
            $response->setValue('location.accuracy', GeoResponse::ACCURACY_POOR);
            break;
        case 'street':
            $response->setValue('address.street', $itemOne['stn']);
            $response->setValue('address.formatted',
                    sprintf('%s, %s %s', $itemOne['stn'], $itemOne['plz'], $response->getValue('address.municipality')));
            $response->setValue('location.wgs84',
                    $response->buildCoordinates($itemOne['avg']['lon'], $itemOne['avg']['lat']));
            $response->setValue('location.bbox',
                    $response->buildBoundingBox($itemOne['northwest']['lon'], $itemOne['northwest']['lat'],
                            $itemOne['southeast']['lon'], $itemOne['southeast']['lat']));
            $response->setValue('location.accuracy', GeoResponse::ACCURACY_STREET);
            break;
        case 'house':
            $response->setValue('address.formatted',
                    sprintf('%s %s%s, %s %s', $itemOne['stn'], $itemOne['hnr'], $itemOne['adz'], $itemOne['plz'],
                        $response->getValue('address.municipality')));
            $response->setValue('address.street', $itemOne['stn']);
            $response->setValue('address.house', $itemOne['hnr']);
            $response->setValue('address.houseext', $itemOne['adz']);
            $response->setValue('location.wgs84',
                    $response->buildCoordinates($itemOne['etrs89']['lon'], $itemOne['etrs89']['lat']));
            $response->setValue('location.accuracy', GeoResponse::ACCURACY_FINE);
            break;
        }
    }


    /**
     *
     *
     * @param string $municipality
     * @param string $district
     * @return string
     */
    public function getAdministrativeDistrict($municipality, $district)
    {
        $map = AgaviConfig::get('modules.geo.administrative-districts', array());
        return isset($map[$municipality][$district]) ? $map[$municipality][$district] : '';
    }

    /**
     *
     *
     * @param string $municipality
     * @param string $district
     * @return string
     */
    public function getDistrict($municipality, $district)
    {
        $map = AgaviConfig::get('modules.geo.districts', array());
        return isset($map[$municipality][$district]) ? $map[$municipality][$district] : '';
    }
}
