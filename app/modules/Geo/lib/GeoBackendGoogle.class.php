<?php

/**
 *
 * Documentation at: https://developers.google.com/maps/documentation/geocoding/#GeocodingRequests
 *
 * @author tay
 * @since 14.11.2012
 *
 */
class GeoBackendGoogle extends GeoBackendBase
{
    const BACKEND = "google";

    /**
     *
     * @var array current google api response
     */
    protected $data = NULL;

    /**
     *
     * @var array translation table between google api response type names and georesponse field names
     */
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

    /**
     *
     * @var unknown_type
     */
    protected $componentsMap =
        array(
            'country' => 'country', 'city' => 'locality', 'postal' => 'postal_code'
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
     * @param GeoRequest $req
     * @return TRUE
     *
     * @throws GeoException
     */
    public function query(GeoRequest $req)
    {
        $this->data = NULL;

        $components = array();
        $query = $req->get('query');
        foreach ($req->toArray() as $key => $value)
        {
            if (!empty ($value) && FALSE === strpos($query, $value))
            {
                $query .= ', ' . $value;
            }
        }

        if ('' == $req->get('country'))
        {
            $components[] = 'country:Deutschland';
        }

        $params =
            array(
                'sensor' => 'false',
                'language' => 'de_DE',
                'address' => trim($query),
                'components' => join('|', $components)
            );

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

        $__logger = AgaviContext::getInstance()->getLoggerManager();
        $__logger->log(__METHOD__ . ":" . __LINE__ . " : " . __FILE__, AgaviILogger::DEBUG);
        $__logger->log($url, AgaviILogger::DEBUG);
        //         $__logger->log($resp, AgaviILogger::DEBUG);

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

        if (!array_key_exists('status', $data) || ($data['status'] != 'OK' && $data['status'] != 'ZERO_RESULTS'))
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
        if (!is_array($this->data) || empty($this->data['results'][0]))
        {
            return FALSE;
        }
        $data = $this->data['results'][0];

        $response->setValue('meta.source', self::BACKEND);
        $response->setValue('meta.timestamp', time() * 1000);
        $response->setValue('meta.date', date(DATE_ISO8601));

        if (!empty($data['address_components']))
        {
            foreach ($data['address_components'] as $part)
            {
                if (empty($part['types']) || empty($part['long_name']))
                {
                    continue;
                }
                $type = join('.', $part['types']);
                if (array_key_exists($type, $this->addressMap))
                {
                    $response->setValue($this->addressMap[$type], $part['long_name']);
                }
            }
        }

        if (!empty($data['formatted_address']))
        {
            $response->setValue('address.formatted', $data['formatted_address']);
            $response->setValue('meta.description', $data['formatted_address']);
        }

        if (!empty($data['geometry']['location_type']))
        {
            $response->setValue('location.accuracy',
                    array_key_exists($data['geometry']['location_type'], $this->accuracyMap)
                        ? $this->accuracyMap[$data['geometry']['location_type']]
                        : GeoResponse::ACCURACY_CRAP);
        }

        if (!empty($data['geometry']['location']))
        {
            $response->setValue('location.wgs84',
                    $response->buildCoordinates($data['geometry']['location']['lng'],
                            $data['geometry']['location']['lat']));
        }

        if (!empty($data['geometry']['bounds']))
        {
            $bounds = $data['geometry']['bounds'];
            $response->setValue('location.bbox',
                    $response->buildBoundingBox($bounds['southwest']['lng'], $bounds['northeast']['lat'],
                            $bounds['northeast']['lng'], $bounds['southwest']['lat']));
        }

        return TRUE;
    }

}
