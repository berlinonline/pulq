<?php

/**
 *
 *
 * @author tay
 * @since 14.11.2012
 *
 */
class YahooGeoBackend extends GeoBackendBase
{
    protected $data = NULL;

    protected $addressMap =
        array(
            'house' => 'address.house',
            'street' => 'address.street',
            'neighborhood' => 'address.district',
            'city' => 'address.municipality',
            'state' => 'address.state',
            'country' => 'address.country',
            'postal' => 'address.postal-code'
        );

    /**
     * query yahoo geocoder
     *
     * Fetch response with {@see fillResponse()} after successful call
     *
     * @param string $query
     * @param array $filter
     *
     * @return TRUE
     *
     * @throws GeoException
     */
    public function query($query, array $filter = array())
    {
        $this->data = NULL;

        $params =
            array(
                'locale' => 'de_DE',
                'gflags' => 'A',
                'flags' => 'J',
                // https://developer.apps.yahoo.com/projects using google bowebmaster@googlemail.com
                'appid' => 'dVmSc4_V34GNxrz4T2P0uWeOH0EuWJUD.SA.lTdBBGf1PHDUFdmFzYubKAmw6TigU6rjX1PhxWLUsu1xlwYipVlYeVk_BB0-',
                'country' => 'DE',
                'city' => 'Berlin',
                'q' => preg_replace('/str\.?\b/i', 'straße', $query)
            );

        if (!empty($filter))
        {
            $components = array();
            foreach ($filter as $key => $value)
            {
                if (!preg_match('/^(?:country|city|postal|street|house)$/', $key))
                {
                    throw new GeoException('Not supported filter: ' . $key, GeoException::GOOGLE_COMPONENTS_FORMAT);
                }
                $params[$key] = $value;
            }
        }

        $url = 'http://where.yahooapis.com/geocode?' . http_build_query($params);
        $ch = $this->curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/javascript'
        ));


        $resp = curl_exec($ch);
        if (200 != curl_getinfo($ch, CURLINFO_HTTP_CODE))
        {
            $error = "'$url' failed with: " . curl_error($ch);
            curl_close($ch);
            $__logger = AgaviContext::getInstance()->getLoggerManager();
            $__logger->log(__METHOD__ . ":" . __LINE__ . " : " . __FILE__, AgaviILogger::ERROR);
            $__logger->log($error, AgaviILogger::ERROR);
            throw new GeoException($error, GeoException::NETWORK_ERROR);
        }

        curl_close($ch);
        $data = json_decode($resp, TRUE);
        if (!is_array($data) || json_last_error() != JSON_ERROR_NONE)
        {
            $__logger = AgaviContext::getInstance()->getLoggerManager();
            $__logger->log(__METHOD__ . ":" . __LINE__ . " : " . __FILE__, AgaviILogger::ERROR);
            $__logger->log($url, AgaviILogger::ERROR);
            $__logger->log($resp, AgaviILogger::ERROR);
            throw new GeoException('No valid json response from yahoo api!', GeoException::INVALID_BACKEND_RESPONSE);
        }

        if (!array_key_exists('Error', $data) || $data['Error'] !== 0)
        {
            $__logger = AgaviContext::getInstance()->getLoggerManager();
            $__logger->log(__METHOD__ . ":" . __LINE__ . " : " . __FILE__, AgaviILogger::ERROR);
            $__logger->log($url, AgaviILogger::ERROR);
            $__logger->log(print_r($data, 1), AgaviILogger::ERROR);
            throw new GeoException('No valid json response from yahoo api!', GeoException::INVALID_BACKEND_RESPONSE);
        }

        if (empty($this->data['Results'][0]))
        {
            $__logger = AgaviContext::getInstance()->getLoggerManager();
            $__logger->log(__METHOD__ . ":" . __LINE__ . " : " . __FILE__, AgaviILogger::ERROR);
            $__logger->log($url, AgaviILogger::ERROR);
            $__logger->log(print_r($data, 1), AgaviILogger::ERROR);
            throw new GeoException('No valid json response from yahoo api!', GeoException::INVALID_BACKEND_RESPONSE);
        }

        $this->data = $data;
        return TRUE;
    }


    /**
     * get last response
     *
     * @param GeoResponse $response
     * @throws GeoException
     */
    public function fillResponse(GeoResponse $response)
    {
        if (!is_array($this->data))
        {
            throw new GeoException('Empty response', GeoException::INTERNAL_ERROR);
        }

        $result = $this->data['Results'][0];

        $response->setValue('meta.source', 'yahoo');
        $response->setValue('meta.timestamp', time() * 1000);
        $response->setValue('meta.date', date(DATE_ISO8601));
        $response->setValue('meta.cache', FALSE);

        foreach ($this->data['Results'][0] as $key => $value)
        {
            if (array_key_exists($key, $this->addressMap))
            {
                $response->setValue($this->addressMap[$key], $value);
            }
        }

        $formatted =
            preg_replace('/,\s*,\s*/', ', ',
                ($result['line1'] . ', ' . $result['line2'] . ', ' . $result['line3'] . ', ' . $result['line4']));
        $response->setValue('address.formated', $formatted);
        $response->setValue('meta.description', $formatted);

        if (($result['radius']/$result['quality']) < 6)
        {
            $response->setValue('location.accuracy', GeoResponse::ACCURACY_GOOD);
        }
        elseif (($result['radius']/$result['quality']) < 7)
        {
            $response->setValue('location.accuracy', GeoResponse::ACCURACY_STREET);
        }
        elseif ($result['radius'] < 4000)
        {
            $response->setValue('location.accuracy', GeoResponse::ACCURACY_POOR);
        }
        else
        {
            $response->setValue('location.accuracy', GeoResponse::ACCURACY_CRAP);
        }

        $response->setValue('location.wgs84',
                    $response->buildCoordinates($result['longitude'], $result['latitude']));
    }
}
