<?php

/**
 * The IComparable interface provides a contract that objects cann fullfill to compare each other.
 *
 * @version         $Id: IComparable.iface.php 1014 2012-03-02 21:39:56Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      DataObject
 */
interface IComparable
{
    /**
     * Return -1 if smaller, 0 if equal and 1 if bigger.
     *
     * @return int
     */
    public function compareTo($other);
}

?>