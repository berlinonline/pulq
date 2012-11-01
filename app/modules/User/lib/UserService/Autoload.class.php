<?php


/**
  * Autoloader for UserService
  *
  * @package UserService
  * @subpackage Core
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */

class UserService_Autoload
{
    private static $basepath = NULL;

    static public function load($name)
    {
        if (0 !== strpos($name, 'UserService_'))
        {
            return FALSE;
        }
        $name = str_replace('_','/', $name);
        return require(self::$basepath.$name.'.php');
    }

    /**
     * register autoload function for the user service models
     */
    static public function register()
    {
        if (NULL === self::$basepath)
        {
            $path = dirname(__FILE__);
            $path = preg_replace('#[^/]+$#', '', $path);
            self::$basepath = $path;
            spl_autoload_register('UserService_Autoload::load');
        }
    }
}
