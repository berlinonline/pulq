<?php

/**
 * The ProjectCurl class is a convenience wrapper around php's curl library.
 * It's job is to create curl handles thereby using system defined settings to init.
 *
 * @version         $Id: ProjectCurl.class.php 1060 2012-03-30 10:32:54Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      Http
 */
class ProjectCurl
{
    /**
     * The default timeout to use for curl handles created by this class.
     */
    const DEFAULT_TIMEOUT = 30;

    /**
     * Create a standard curl handle,
     * thereby initializing it with several config driven parameters
     * and sane defaults.
     *
     * Supported parameters from the app/config/settings.xml
     * <ul>
     * <li>curl.verbose - defaults to false
     * <li>curl.proxy - defaults to ''
     * <li>curl.timeout - defaults to DEFAULT_TIMEOUT
     * </ul>
     *
     * @param string $url An optional url to init the handle with.
     *
     * @return Resource A freshly initialized curl handle.
     */
    public static function create($url = NULL)
    {
        $curlHandle = curl_init();

        curl_setopt($curlHandle, CURLOPT_VERBOSE, AgaviConfig::get('curl.verbose', 0));
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlHandle, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($curlHandle, CURLOPT_FRESH_CONNECT, 0);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curlHandle, CURLOPT_PROXY, AgaviConfig::get('curl.proxy', ''));
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, AgaviConfig::get('curl.timeout', self::DEFAULT_TIMEOUT));
        curl_setopt($curlHandle, CURLOPT_ENCODING, 'gzip,deflate');

        if ($url)
        {
            curl_setopt($curlHandle, CURLOPT_URL, $url);
        }
        return $curlHandle;
    }
}

?>
