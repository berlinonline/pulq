<?php

namespace Pulq\Data;

use Pulq\Exceptions\ArrayScopeException;
use Pulq\Exceptions\InvalidArgumentException;

abstract class BaseDataObject implements IDataObject
{
    /**
     * Create a new IDataObject instance from the given data.
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->hydrate($data);
    }

    /**
     * Convenience method for setting multiple values at once.
     *
     * @param array $values
     */
    public function applyValues(array $values)
    {
        foreach ($values as $propName => $value)
        {
            $setterName = 'set'.ucfirst($propName);
            $class = new ReflectionClass($this);
            if ($class->hasProperty($propName) && $class->hasMethod($setterName))
            {
                $method = $class->getMethod($setterName);
                if ($method->isPublic())
                {
                    $method->invoke($this, $value);
                }
            }
        }
    }

    /**
     * Returns an array representation of the location.
     *
     * @return string
     */
    public function toArray($scopeName = null)
    {
        $scopeName = is_null($scopeName) ? $this->getDefaultArrayScope() : $scopeName;

        $scope = $this->getArrayScope($scopeName);

        $data = array();
        // iterate over our exposed properties
        foreach ($scope as $prop)
        {
            // either call a getter or just grab the value foreach property
            $getter = 'get' . ucfirst($prop);
            $propValue = NULL;
            if (is_callable(array($this, $getter)))
            {
                $propValue = $this->$getter();
            }
            else
            {
                $propValue = $this->$prop;
            }
            // handle value conversion if required
            if ($propValue instanceof IDataObject)
            {
                $propValue = $propValue->toArray($scopeName);
            }
            elseif(is_array($propValue))
            {
                $propValue = $this->convertArray($propValue);
            }
            // then assign and go on with the next...
            $data[$prop] = $propValue;
        }

        return $data;
    }

    protected function getDefaultArrayScope()
    {
        return 'list';
    }

    protected function getArrayScope($scopeName)
    {
        $scopes = $this->getArrayScopes();

        if (!array_key_exists($scopeName, $scopes))
        {
            throw new ArrayScopeException("Object of class" . get_class($this) . " has no scope '$scopeName'");
        }

        return $scopes[$scopeName];
    }

    /**
     * Converts all elements in the given array into a scalar representation.
     *
     * @param array $array The array which's values shall be converted.
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    protected function convertArray(array $array)
    {
        $tempArray = array();
        foreach ($array as $key => $value)
        {
            if ($value instanceof IDataObject)
            {
                $tempArray[$key] = $value->toArray();
            }
            elseif (is_array($value))
            {
                $tempArray[$key] = $this->convertArray($value);
            }
            else if ($value instanceof IObjectAllowedInScopedArray)
            {
                $tempArray[$key] = $value;
            }
            else
            {
                if (is_object($value) && !in_array(get_class($value), $this->getObjectTypeWhitelist()))
                {
                    throw new InvalidArgumentException(
                        "Invalid array value encountered, " .
                        "while creating an array representation of the current instance.\n" .
                        "Be sure to only compose IDataObjects in your IDataObjects.\n" .
                        "See the IDataObject api doc for further information."
                    );
                }
                $tempArray[$key] = $value;
            }
        }
        return $tempArray;
    }

    /**
     * Hydrates the given data into the item.
     * This method is used to internally setup our state
     * and has privleged write access to all properties.
     * Properties that are set during hydrate dont mark the item as modified.
     *
     * @param array $data
     */
    protected function hydrate(array $data)
    {
        $this->hydrateActive = TRUE;
        foreach ($this->getPropertyNames() as $prop)
        {
            if (array_key_exists($prop, $data))
            {
                $setter = 'set'.ucfirst($prop);
                if (is_callable(array($this, $setter)))
                {
                    $this->$setter($data[$prop]);
                }
                else
                {
                    $this->$prop = $data[$prop];
                }
            }
        }
        $this->hydrateActive = FALSE;
    }

    /**
     * Return a list of property names of properties
     * that shall be processed by our toArray and fromArray methods.
     * As this is a data-object the default implementation will return all properties
     * of the current instance, because data objects hold data (right?) and in most use cases
     * data-objects will not contain additional non-data members.
     * For the once in a while occasion add any non-data members to the blacklist.
     * @see self::getPropertyBlacklist()
     *
     * @return array An array of property names as strings.
     */
    protected function getPropertyNames()
    {
        $blacklist = $this->getPropertyBlacklist();
        return array_filter(array_keys(
            get_class_vars(get_class($this))
        ), function($item) use ($blacklist)
        {
            return ! in_array($item, $blacklist);
        });
    }

    /**
     * Return a list of property names of properties that shall be
     * excluded from processing by the toArray and fromArray methods.
     *
     * @return array An array of property names as strings.
     */
    protected function getPropertyBlacklist()
    {
        return array();
    }

    /**
     * Tells whether this instance is currently in the process of hydrating data.
     *
     * @return boolean
     */
    protected function isHydrating()
    {
        return $this->hydrateActive;
    }

    /**
     * Stub for defining the scopes of a data object.
     * needs to be implemented by the actual object
     *
     * @return array The supported scopes
     */
    abstract protected function getArrayScopes();

    public function __call($methodName, $arguments)
    {
        $matches = array();
        if (preg_match('/(get|set)(\w+)/', $methodName, $matches))
        {
            $accessMode = $matches[1];
            $fieldName = lcfirst($matches[2]);

            if ($accessMode === 'set')
            {
                $this->$fieldName = $arguments[0];
            }
            else
            {
                return $this->$fieldName;
            }
        }
    }

    public static function fromArray(array $data = array())
    {
        return new static($data);
    }

    protected function getObjectTypeWhitelist(array $additionalTypes = array()) {
        return array_merge(array(
            'DateTime'
        ), $additionalTypes);
    }
}
