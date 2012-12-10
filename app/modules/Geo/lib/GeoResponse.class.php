<?php

/**
 *
 *
 * @author tay
 * @since 14.11.2012
 *
 */
class GeoResponse
{
    /**
     * Housenumber
     */
    const ACCURACY_FINE = 1;
    /**
     * interpolated position between housnumbers
     */
    const ACCURACY_GOOD = 2;
    /**
     * hole street - center of street
     */
    const ACCURACY_STREET = 3;
    /**
     * district, ...
     */
    const ACCURACY_POOR = 9;
    /**
     *
     */
    const ACCURACY_CRAP = 10;

    /**
     *
     * @var array definition of result structure
     */
    private $result =
        array(
            'address' => array(
                'country' => '',
                'state' => '',
                'municipality' => '',
                'administrative-district' => '',
                'district' => '',
                'urban-subdivision' => '',
                'postal-code' => '',
                'street' => '',
                'house' => '',
                'houseext' => '',
                'formatted' => ''
            ),
            'location' => array(
                'wgs84' => array(
                    'lon' => 0,
                    'lat' => 0
                ),
                'bbox' => array(
                    'northwest' => array(), 'southeast' => array()
                ),
                'accuracy' => self::ACCURACY_CRAP,
            ),
            'meta' => array(
                'source' => '',
                'description' => '',
                'tags' => '',
                'timestamp' => 0,
                'date' => '',
                'cached' => FALSE,
                'copyright' => '',
            )
        );

    /**
     *
     * @var GeoException last exception thrown an stored
     */
    protected $error;

    /**
     *
     * @var array
     */
    protected $validators = array(
            'wgs84' => 'wgs84'
        );


    /**
     *
     *
     * @param int $apiLevel API Level
     * @return GeoResponse
     */
    static public function getInstanceForApi($apiLevel = 1)
    {
        $class = AgaviConfig::get("modules.geo.api.$apiLevel.response-class", __CLASS__);
        $response = new $class();
        return $response;
    }

    /**
     *
     *
     * @param array $result
     * @return GeoResponse
     */
    static public function getInstanceForResult(array $result)
    {
        /* @todo Remove debug code GeoResponse.class.php from 10.12.2012 */
        $__logger=AgaviContext::getInstance()->getLoggerManager();
        $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__,AgaviILogger::DEBUG);
        $__logger->log(print_r($result,1),AgaviILogger::DEBUG);

        $class = $result['meta']['class'];
        $response = new $class();
        $response->setRawResult($result);
        return $response;
    }


    /**
     *
     *
     * @param array $result
     */
    protected function setRawResult(array $result)
    {
        $this->result = $result;
    }


    /**
     * get single response value
     *
     * @param string $field
     * @return mixed
     */
    public function getValue($field)
    {
        list($base, $part) = $this->checkFieldName($field);
        return isset($this->result[$base][$part]) ? $this->result[$base][$part] : NULL;
    }


    /**
     * set value in result
     *
     * @param string $field
     * @param mixed $value
     *
     * @throws GeoException
     */
    public function setValue($field, $value)
    {
        list($base, $part) = $this->checkFieldName($field);
        $message =
            $this->validate("$base.$part", $value,
                    isset($this->result[$base][$part]) ? $this->result[$base][$part] : NULL);
        if ('OK' != $message)
        {
            throw new GeoException($message, GeoException::INVALID_RESULT_VALUE);
        }
        if (! empty($value))
        {
            $this->result[$base][$part] = $value;
        }
        return TRUE;
    }


    /**
     *
     *
     * @return boolean
     */
    public function isFilled()
    {
        return $this->result['location']['wgs84']['lat'] != 0 || $this->result['location']['wgs84']['lon'] != 0;
    }

    /**
     * set value in result
     *
     * works like {@see setValue()} but catches exceptions. Retrieve thrown exceptions with {@see getLastError()}
     *
     * @param string $field
     * @param mixed $value
     */
    public function setValueForce($field, $value)
    {
        try
        {
            $this->error = NULL;
            return $this->setValue($field, $value);
        }
        catch (GeoException $e)
        {
            $this->error = $e;
            return FALSE;
        }
    }

    /**
     *
     *
     * @return multitype:multitype: multitype:string
     */
    public function toArray()
    {
        $this->result['meta']['class'] = get_class($this);
        $this->result['meta']['cached'] = TRUE;
        return $this->result;
    }

    /**
     * get last thrown and stored exception
     *
     * @see setValueForce()
     * @return GeoException or NULL
     */
    public function getLastError()
    {
        return $this->error;
    }

    /**
     * build coordinate value array
     *
     * @param float $longitude
     * @param float $latitude
     * @return array
     */
    public function buildCoordinates($longitude, $latitude)
    {
        return array(
            'lon' => $longitude, 'lat' => $latitude
        );
    }


    /**
     * build bounding box value array
     *
     * @param float $nw_longitude    north west longitude of box
     * @param float $nw_latitude     north west latitude of box
     * @param float $se_longitude    south east longitude of box
     * @param float $se_latitude     south east latitude of box
     * @return array
     */
    public function buildBoundingBox($nw_longitude, $nw_latitude, $se_longitude, $se_latitude)
    {
        return array(
            'northwest' => $this->buildCoordinates($nw_longitude, $nw_latitude),
            'southeast' => $this->buildCoordinates($se_longitude, $se_latitude)
        );
    }


    /**
     *
     */
    protected function validate($field, $value, $current)
    {
        if (is_array($current))
        {
            if (!is_array($value))
            {
                return "Value for field '$field' must be an array: ".var_export($value,1);
            }
            if (!empty($current)
                && array_keys($current) != array_keys($value))
            {
                return "Value for field '$field' must use the same array keys: "
                        . join(',', array_keys($current))
                        . " != "
                        . join(',', array_keys($value));
            }
        }
        elseif (!is_scalar($value) && !is_null($value))
        {
            return "Invalid value for field '$field': " . var_export($value, 1);
        }

        if (array_key_exists($field, $this->validators))
        {
            $validator = $this->validators[$field];
            if ($validator[0] == '!')
            {
                $method = 'validate' . ucfirst(substr($validator, 1));
                if (method_exists($this, $method))
                {
                    $message = $this->$method($value);
                    if ('OK' != $message)
                    {
                        return $message;
                    }
                }
            }
            elseif ($validator[0] == '/')
            {
                if (! preg_match($validator, $value))
                {
                    return 'Value for field does not match requirements: '.var_export($value,1);
                }
            }
        }
        return 'OK';
    }


    /**
     * validates wgs84 value array
     *
     * @return string
     */
    protected function validateWgs84($value)
    {
        if (is_array($value) && count($value) == 2)
        {
            if (!is_float($value['lon']) && $value['lon'])
            {
                return 'Invalid wgs84::longitude';
            }
            if (!is_float($value['lat']) && $value['lat'])
            {
                return 'Invalid wgs84::longitude';
            }
            return 'OK';
        }
        return 'Invalid wgs84 value array';
    }


    /**
     * check if field name exists
     *
     * @param string $field field name
     * @throws GeoException
     */
    protected function checkFieldName($field)
    {
        list($base, $part) = explode('.', $field, 2);
        if (!isset($part))
        {
            $part = $base;
            $base = 'INVALID';
            foreach ($this->result as $tmp => $sub)
            {
                if (is_array($sub) && array_key_exists($part, $sub))
                {
                    $base = $tmp;
                    break;
                }
            }
        }

        if (!array_key_exists($base, $this->result) || !isset($this->result[$base][$part]))
        {
            throw new GeoException("Invalid result field: '$field'", GeoException::INVALID_RESULT_SECTION);
        }

        return array(
            $base, $part
        );
    }


}
