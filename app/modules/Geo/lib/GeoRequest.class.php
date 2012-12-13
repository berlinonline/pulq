<?php

/**
 *
 *
 * @author tay
 * @since 30.11.2012
 *
 */
class GeoRequest
{
    /**
     *
     * our API version; overwrite class to implement newer API levels
     */
    protected $apiVersion = 1;

    /**
     *
     * @var main request data
     */
    protected $req = array();


    /**
     *
     *
     * @param array $req
     * @param int $apiLevel
     *
     * @return GeoRequest
     */
    public static function getInstanceForApi(array $req = array(), $apiLevel = 1)
    {
        $class = AgaviConfig::get("modules.geo.api.$apiLevel.request-class", __CLASS__);
        return new $class($req);
    }


    /**
     *
     *
     * @param array $req
     */
    public function __construct(array $req)
    {
        $this->resetAll();
        foreach ($this->req as $key => $v)
        {
            if (array_key_exists($key, $req))
            {
                $this->set($key, $req[$key]);
            }
        }
    }

    /**
     * init the request with empty data
     *
     */
    protected function resetAll()
    {
        $this->req =
            array(
                'query' => '', 'country' => '', 'city' => '', 'street' => '', 'house' => '', 'postal' => ''
            );
    }

    /**
     *
     *
     * @return array of valid request keys
     */
    public function getValidKeys()
    {
        return array_keys($this->req);
    }

    /**
     *
     *
     * @param string $key
     * @param string $value
     * @throws GeoException
     * @return GeoRequest
     */
    public function set($key, $value)
    {
        if (!array_key_exists($key, $this->req))
        {
            throw new GeoException("invalid request key: '$key'", GeoException::INTERNAL_ERROR);
        }
        if (!is_scalar($value))
        {
            throw new GeoException('value must be a scalar', GeoException::INTERNAL_ERROR);
        }
        $this->req[$key] = trim($value);
        return $this;
    }

    /**
     *
     *
     * @param string $key
     */
    public function has($key)
    {
        return !empty($this->req[$key]);
    }

    /**
     * get request value by key
     *
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        return array_key_exists($key, $this->req) ? $this->req[$key] : FALSE;
    }


    /**
     *
     *
     * @return boolean
     */
    public function isParsed()
    {
        $rdata = $this->req;
        unset($rdata['query']);
        return '' !== implode('', $rdata);
    }


    /**
     *
     */
    public function hash()
    {
        return sha1(serialize(array(
            'api' => $this->apiVersion
        ) + $this->req));
    }

    /**
     *
     *
     * @return multitype:
     */
    public function toArray()
    {
        return $this->req;
    }

    /**
     *
     *
     * @return multitype:
     */
    public function _forCache()
    {
        return $this->req;
    }
}
