<?php
/**
 *
 *
 * @author tay
 * @since 14.11.2012
 *
 */
class GoogleGeoBackend extends GeoBackendBase
{

    protected $data = NULL;

    protected $addressMap =
        array(
            'street_number' => 'address.house',
            'route' => 'address.street',
            'sublocality.political' => 'address.district',
            'administrative_area_level_3.political' => 'address.district',
            'locality.political' => 'address.municipality',
            'administrative_area_level_1.political' => 'address.state',
            'country.political' => 'address.country',
            'postal_code' => 'address.postal-code'
        );

    protected $componentsMap =
        array(
                'country' => 'country',
                'city' => 'locality',
                'postal' => 'postal_code',
                'street' => 'route',
                // 'house' => FALSE
                );

    protected $accuracyMap =
        array(
            // housenumber
            'ROOFTOP' => GeoResponse::ACCURACY_FINE,
            // between housenumbers
            'RANGE_INTERPOLATED' => GeoResponse::ACCURACY_GOOD,
            // street
            'GEOMETRIC_CENTER' => GeoResponse::ACCURACY_STREET,
            // poor
            'APPROXIMATE' => GeoResponse::ACCURACY_POOR
        );

    /**
     * query google geocoder
     *
     * Fetch response with {@see fillResponse()} after successful call
     *
     * @param string $query
     * @param array $filter supported keys are country, city, postal, street
     *
     * @return TRUE
     *
     * @throws GeoException
     */
    public function query($query, array $filter = array())
    {
        $this->data = NULL;

        $params = array(
                'sensor' => 'false', 'language' => 'de_DE', 'address' => $query
            );

        if (!empty($filter))
        {
            $components = array();
            foreach ($filter as $key => $value)
            {
                if (!preg_match('/^(?:country|city|postal|street|house)$/', $key))
                {
                    throw new GeoException('Not supported components filter: ' . $key,
                        GeoException::GOOGLE_COMPONENTS_FORMAT);
                }
                if (isset($this->componentsMap[$key]))
                {
                    $components[] = $this->componentsMap[$key] . ":$value";
                }
            }
            $params['components'] = join('|', $components);
        }

        $url = 'http://maps.googleapis.com/maps/api/geocode/json?' . http_build_query($params);
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
            throw new GeoException('No valid json response from google api!', GeoException::INVALID_BACKEND_RESPONSE);
        }

        if (!array_key_exists('status', $data) || $data['status'] != 'OK')
        {
            $__logger = AgaviContext::getInstance()->getLoggerManager();
            $__logger->log(__METHOD__ . ":" . __LINE__ . " : " . __FILE__, AgaviILogger::ERROR);
            $__logger->log($url, AgaviILogger::ERROR);
            $__logger->log(print_r($data, 1), AgaviILogger::ERROR);
            throw new GeoException('No valid json response from google api!', GeoException::INVALID_BACKEND_RESPONSE);
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

        $response->setValue('meta.source', 'google');
        $response->setValue('meta.timestamp', time() * 1000);
        $response->setValue('meta.date', date(DATE_ISO8601));
        $response->setValue('meta.cache', FALSE);

        if (!empty($this->data['address_components']))
        {
            foreach ($this->data['address_components'] as $part)
            {
                if (empty($part['types']) || empty($part['long_name']))
                {
                    continue;
                }
                $type = join('.', $part['types']);
                if (array_key_exists($type, $this->addressMap))
                {
                    $response->setValue($field, $part['long_name']);
                }
            }
        }

        if (!empty($this->data['formatted_address']))
        {
            $response->setValue('address.formated', $this->data['formatted_address']);
            $response->setValue('meta.description', $this->data['formatted_address']);
        }

        if (!empty($this->data['geometry']['location_type']))
        {
            $response->setValue('location.accuracy',
                    array_key_exists($this->data['geometry']['location_type'], $this->accuracyMap)
                        ? $this->accuracyMap[$this->data['geometry']['location_type']]
                        : GeoResponse::ACCURACY_CRAP);
        }

        if (!empty($this->data['geometry']['location']))
        {
            $response->setValue('location.wgs84',
                    $response->buildCoordinates($this->data['geometry']['location']['lng'],
                            $this->data['geometry']['location']['lat']));
        }

        if (!empty($this->data['geometry']['bounds']))
        {
            $response->setValue('location.bbox',
                    $response->buildBoundingBox($nw_longitude, $nw_latitude, $se_longitude, $se_latitude));
        }
    }
}
