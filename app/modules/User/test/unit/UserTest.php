<?php

require_once('AbstractTest.class.php');

/**
  * Zum Testen der RestOn-Request-Klassen
  *
  * @package Unittest
  * @subpackage RestOn
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */

class UserTest extends AbstractTest
{


    /**
      * Teste die Initialisierung und pruefe daraufhin das Interface
      *
      */
    public function testInterface ()
    {
        $db = new UserService_User();
        $this->assertTrue($db instanceof UserService_User_Interface, "Invalid Interface for User");
    }


    /**
      * Test password functions
      */
    public function testPassword ()
    {
        $user = new UserService_User();
        $user->setPassword('test');
        $this->assertTrue($user->verifyPassword('test'), "Passwort could not be verified");
    }




}
?>