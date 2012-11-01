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

class GroupTest extends AbstractTest
{


    /**
      * Teste die Initialisierung und pruefe daraufhin das Interface
      *
      */
    public function testInterface ()
    {
        $db = new UserService_Group('superuser');
        $this->assertTrue($db instanceof UserService_Group_Interface, "Invalid Interface for Group");
    }




}
?>