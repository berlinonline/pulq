<?php

/**
 * The IDataObject interface is the interface that shall be implemented by all objects
 * that serve as data-holder or -transfer objects.
 * When passing the result from toArray to fromArray,
 * then the returned IDataObject should be in the same state
 * as the origin object that toArray was called on.
 * To keep things simple IDataObjects should only compose other IDataObjects or scalar values
 * and should not define (much)behaviour.
 *
 * @version $Id: IDataObject.iface.php 1035 2012-03-12 11:14:17Z tschmitt $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Project
 * @subpackage DataObject
 */
interface IDataObject
{
    /**
     * Return an array representation of the current IDataObject.
     *
     * @return array
     */
    public function toArray();

    /**
     * Convenience method for setting multiple values at once.
     *
     * @param array $values
     */
    public function applyValues(array $values);

    /**
     * Create a new IDataObject instance from the given data.
     *
     * @param array $data The data to create the object from.
     *
     * @return IDataObject The fresh IDataObject instance.
     */
    public static function fromArray(array $data = array());
}

?>
