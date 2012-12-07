<?php

abstract class GeoBackendBase
{
    const BACKEND = 'base';

   /**
     * replaces the php (core) method for initializing curl handles.
     *
     * The method initializes a curl handle as curl_init() does and
     * sets some useful defaults for proxy, timeout, returntransfer and debugging.
     *
     * Used settings: core.curl.proxy, core.curl.timeout, core.debug
     *
     * @see curl_init()
     * @param string$url URL to fetch
     * @param AgaviWebRequest $request the current request for setup referer; defaults to NULL
     * @return curl handle
     */
    public function curl_init($url, AgaviWebRequest $request = NULL)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_PROXY, AgaviConfig::get('core.curl.proxy', ''));
        curl_setopt($ch, CURLOPT_VERBOSE, AgaviConfig::get('core.debug', 0));
        curl_setopt($ch, CURLOPT_TIMEOUT, AgaviConfig::get('core.curl.timeout', 5));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        if ($request instanceof AgaviWebRequest)
        {
            curl_setopt($ch, CURLOPT_REFERER, $request->getUrl());
        }
        return $ch;
    }


    /**
     * query remote geocoder
     *
     * Fetch response with {@see fillResponse()} after successful call
     *
     * @param GeoRequest $request
     * @return TRUE
     *
     * @throws GeoException
     */
    abstract public function query(GeoRequest $request);

    /**
     * get last response
     *
     * @param GeoResponse $response
     * @throws GeoException
     */
    abstract public function fillResponse(GeoResponse $response);
}
