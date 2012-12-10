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
        /* @todo Remove debug code AskAction.class.php from 07.12.2012 */
        $__logger=AgaviContext::getInstance()->getLoggerManager();
        $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__,AgaviILogger::DEBUG);
        $__logger->log(print_r($rd->getParameters(),1),AgaviILogger::DEBUG);

        if ($rd->hasParameter('q'))
        {
            $parsed = AddressParser::parse($rd->getParameter('q'));
            if (is_array($parsed))
            {
                $request = GeoRequest::getInstanceForApi($parsed, $rd->getParameter('api', 1));
            }
            else
            {
                $request =
                    GeoRequest::getInstanceForApi(array(
                        'query' => $rd->getParameter('q')
                    ), $rd->getParameter('api', 1));
            }
        }

        foreach (array('country', 'city', 'street', 'house') as $pkey)
        {
            if ($rd->hasParameter($pkey))
            {
                $request->set($pkey, $rd->getParameter($pkey));
            }
        }

        /* @todo Remove debug code AskAction.class.php from 10.12.2012 */
        $__logger=AgaviContext::getInstance()->getLoggerManager();
        $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__,AgaviILogger::DEBUG);
        $__logger->log(print_r($request->toArray(),1),AgaviILogger::DEBUG);

        $cache = new GeoCache();
        $cacheRequest = clone $request;
        $response = $cache->fetch($cacheRequest);
        if (! $response)
        {
            $response = GeoResponse::getInstanceForApi($rd->getParameter('api', 1));
            if (! $request->isParsed())
            {
                $google = new GeoBackendGoogle();
                $google->query($request);
                $google->fillResponse($response);

                // refine the actual request for the HaKaDe query
                $request->set('query', '');
                $request->set('country', $response->getValue('address.country'));
                $request->set('city', $response->getValue('address.municipality'));
                $request->set('postal', $response->getValue('address.postal-code'));
                $request->set('street', $response->getValue('address.street'));
                $request->set('house', $response->getValue('address.house'));
            }
        }

        /* @todo Remove debug code AskAction.class.php from 10.12.2012 */
        $__logger=AgaviContext::getInstance()->getLoggerManager();
        $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__,AgaviILogger::DEBUG);
        $__logger->log(print_r($request->toArray(),1),AgaviILogger::DEBUG);

        $backend = new GeoBackendHaKoDe();
        $backend->query($request);
        $backend->fillResponse($response);

        if (! $response->isFilled())
        {
            $yahoo = new GeoBackendYahoo();
            $yahoo->query($request);
            $yahoo->fillResponse($response);
        }

        $cache->put($cacheRequest, $response);

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
        return 'Success';
    }
}

?>