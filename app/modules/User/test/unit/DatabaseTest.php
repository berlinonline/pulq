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

class DatabaseTest extends AbstractTest
{


    private function getDb()
    {
        $pdo = AgaviContext::getInstance()->getDatabaseConnection('UserServiceDB');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db = new UserService_Database($pdo);
        return $db;
    }

    /**
      * Teste die Initialisierung und pruefe daraufhin das Interface
      *
      */
    public function testInterface ()
    {
        $db = $this->getDb();
        $this->assertTrue($db instanceof UserService_Database_Interface, "Invalid Interface for Database");
    }


    /**
      * Create and delete an user
      *
      */
    public function testSimpleOperations ()
    {
        $db = $this->getDb();
        $user = UserService_User::create()->setLoginname('test');
        $replacedUser = $db->replaceUser($user);
        $this->assertTrue($db->existsLogin('test'), "User 'test' does not exist after insert");
        $db->deleteUser($user);
        $this->assertFalse($db->existsLogin('test'), "User 'test' does exist after delete");
    }


    /**
      * Create and fetch an user
      *
      */
    public function testFetch ()
    {
        $db = $this->getDb();
        $user = UserService_User::create()->setLoginname('test');
        $user->setEmail('test@berlinonline.net');
        $replacedUser = $db->replaceUser($user);

        $fetchUserByLoginname = $db->fetchUserByLoginname('test');
        $this->assertTrue($fetchUserByLoginname instanceof UserService_User_Interface, "Invalid interface for fetched user");
        $this->assertTrue($fetchUserByLoginname->getLoginname() === 'test', "Loginname of new user does not match");

        $mailUser = $db->fetchUserByMail('test@berlinonline.net');
        $this->assertTrue($mailUser->getLoginname() === 'test', "Loginname of user fetched by mail address (".$mailUser->getLoginname()."!=test) does not match");

        $allUsers = $db->fetchUsers();
        $this->assertTrue($allUsers[0]->getLoginname() === 'test', "Loginname of first user (".$allUsers[0]->getLoginname()."!=test) does not match, do you use an empty test-DB?");

        $db->deleteUser($user);
    }

    /**
      * Create and fetch an groups
      *
      */
    public function testGroups ()
    {
        $admin = UserService_Group::create('admin');
        $member = UserService_Group::create('member', 'unittest');

        $db = $this->getDb();
        $user = UserService_User::create()->setLoginname('test');
        $user->addGroup($admin);
        $user->addGroup($member);
        $replacedUser = $db->replaceUser($user);
        $fetchedUser = $db->fetchUserByLoginname('test');
        $this->assertTrue($fetchedUser->hasGroup($admin), "Fetched user does not have admin-group");
        $this->assertTrue($fetchedUser->hasGroup($member), "Fetched user does not have member-group");


        $db->deleteUser($user);
    }





}
?>
