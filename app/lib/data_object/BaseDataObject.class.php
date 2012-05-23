<?php

/**
 * The BaseDataObject serves as the base implementation of the IDataObject interface.
 * It implements most of the interface except the fromArray factory method,
 * which must be defined specifically by concrete implementations.
 *
 * @version $Id: BaseDataObject.class.php 1032 2012-03-09 13:41:37Z tschmitt $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Project
 * @subpackage DataObject
 */
abstract class BaseDataObject implements IDataObject
{
    /**
     * Holds an internal flag that is used to indicate
     * that we are currently hydrating.
     * This can come in handy when you are trying to distinguish property change origins.
     * For example during hydrate {@see IDocument}s do not respond to onPropertyChange notifications.
     *
     * @var boolean
     */
    private $hydrateActive = FALSE;

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
    public function toArray()
    {
        $data = array();
        // iterate over our exposed properties
        foreach ($this->getPropertyNames() as $prop)
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
                $propValue = $propValue->toArray();
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
            else
            {
                if (is_object($value))
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
        return array('hydrateActive');
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
}

?>
