<?php

/**
 *
 *
 * @author tay
 * @since 13.11.2012
 *
 */
class Geo_AskAction extends ProjectGeoBaseAction
{

    protected $validBackends =
        array(
            GeoBackendGoogle::BACKEND => TRUE, GeoBackendYahoo::BACKEND => TRUE, GeoBackendHaKoDe::BACKEND => TRUE
        );

    /**
     * Handles the Read request method.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>A string containing the view name associated
     *                     with this action; or</li>
     *                     <li>An array with two indices: the parent module
     *                     of the view to be executed and the view to be
     *                     executed.</li>
     *                   </ul>^
     */
    public function executeRead(AgaviRequestDataHolder $rd)
    {
        $this->prepareValidBackends($rd);
        $request = $this->buildRequest($rd);

        $cache = new GeoCache();
        $cacheRequest = clone $request;
        $response = $cache->fetch($cacheRequest, $rd->getParameter('max-age', GeoCache::DEFAULT_MAX_AGE));
        if ($response)
        {
            $this->setAttribute('response', $response);
            return 'Success';
        }

        $response = GeoResponse::getInstanceForApi($rd->getParameter('api', 1));
        if (!$request->isParsed() && $this->validBackends[GeoBackendGoogle::BACKEND])
        {
            try
            {
                $google = new GeoBackendGoogle();
                $google->query($request);
                $google->fillResponse($response);
            }
            catch (GeoException $e)
            {
                $__logger = AgaviContext::getInstance()->getLoggerManager();
                $__logger->log(__METHOD__ . ":" . __LINE__ . " : " . __FILE__, AgaviILogger::ERROR);
                $__logger->log(print_r($e, 1), AgaviILogger::ERROR);
            }

            // refine the actual request for the HaKaDe query
            $request->set('query', '');
            $request->set('country', $response->getValue('address.country'));
            $request->set('city', $response->getValue('address.municipality'));
            $request->set('postal', $response->getValue('address.postal-code'));
            $request->set('street', $response->getValue('address.street'));
            $request->set('house', $response->getValue('address.house'));
        }

        if (('' == $request->get('city') || 'Berlin' == $request->get('city')
            || preg_match('/\bBerlin\b/i', $request->get('query'))) && $this->validBackends[GeoBackendHaKoDe::BACKEND])
        {
            try
            {
                $backend = new GeoBackendHaKoDe();
                $backend->query($request);
                $backend->fillResponse($response);
            }
            catch (GeoException $e)
            {
                $__logger = AgaviContext::getInstance()->getLoggerManager();
                $__logger->log(__METHOD__ . ":" . __LINE__ . " : " . __FILE__, AgaviILogger::ERROR);
                $__logger->log($e->__toString(), AgaviILogger::ERROR);
            }
        }

        if (!$response->isFilled() && $this->validBackends[GeoBackendGoogle::BACKEND])
        {
            try
            {
                $google = new GeoBackendGoogle();
                $google->query($request);
                $google->fillResponse($response);
            }
            catch (GeoException $e)
            {
                $__logger = AgaviContext::getInstance()->getLoggerManager();
                $__logger->log(__METHOD__ . ":" . __LINE__ . " : " . __FILE__, AgaviILogger::ERROR);
                $__logger->log($e->__toString(), AgaviILogger::ERROR);
            }
        }

        if (!$response->isFilled() && $this->validBackends[GeoBackendYahoo::BACKEND])
        {
            try
            {
                $yahoo = new GeoBackendYahoo();
                $yahoo->query($request);
                $yahoo->fillResponse($response);
            }
            catch (GeoException $e)
            {
                $__logger = AgaviContext::getInstance()->getLoggerManager();
                $__logger->log(__METHOD__ . ":" . __LINE__ . " : " . __FILE__, AgaviILogger::ERROR);
                $__logger->log($e->__toString(), AgaviILogger::ERROR);
            }
        }

        if ($response->isFilled())
        {
            $cache->put($cacheRequest, $response);
        }

        $this->setAttribute('response', $response);

        return 'Success';
    }

    /**
     * Handles the Write request method.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>A string containing the view name associated
     *                     with this action; or</li>
     *                     <li>An array with two indices: the parent module
     *                     of the view to be executed and the view to be
     *                     executed.</li>
     *                   </ul>^
     */
    public function executeWrite(AgaviRequestDataHolder $rd)
    {
        $this->executeRead($rd);
    }


    /**
     *
     *
     * @param AgaviRequestDataHolder $rd
     */
    protected function buildRequest(AgaviRequestDataHolder $rd)
    {
        $request = NULL;
        if ($rd->hasParameter('q'))
        {
            $parsed = AddressParser::parse($rd->getParameter('q'));
            if (is_array($parsed))
            {
                $request =
                    GeoRequest::getInstanceForApi(
                        array_merge(
                            array(
                                'query' => $rd->getParameter('q')
                            ), $parsed), $rd->getParameter('api', 1));
            }
        }

        if (!$request)
        {
            $request =
                GeoRequest::getInstanceForApi(
                    array(
                        'query' => $rd->getParameter('q')
                    ), $rd->getParameter('api', 1));
        }

        foreach (array(
            'country', 'city', 'street', 'house'
        ) as $pkey)
        {
            if ($rd->hasParameter($pkey))
            {
                $request->set($pkey, $rd->getParameter($pkey));
            }
        }

        return $request;
    }


    /**
     *
     *
     * @param AgaviRequestDataHolder $rd
     */
    protected function prepareValidBackends(AgaviRequestDataHolder $rd)
    {
        if ($rd->hasParameter('backends'))
        {
            $white = array();
            $black = array();
            foreach (explode(',', $rd->getParameter('backends')) as $def)
            {
                if ($def[0] == '!')
                {
                    $black[substr($def, 1)] = FALSE;
                }
                else
                {
                    $white[$def] = TRUE;
                }
            }
            if (empty($white))
            {
                $white = $this->validBackends;
            }
            $this->validBackends = array_merge($white, $black);
        }
    }
}

?>