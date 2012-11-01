<?php

/**
  * Abstract base class for tests
  *
  * @package Unittest
  * @subpackage RestOn
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */

abstract class AbstractTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     *
     * @return multitype:
     */
    public function __sleep()
    {
        $out = array();
        foreach (get_object_vars($this) as $name => $val)
        {
            if (is_resource($val))
            {
                continue;
            }
            if ($val instanceof UserService_Database_Interface)
            {
                continue;
            }
            $out[] = $name;
        }
        return $out;
    }

}
?>
