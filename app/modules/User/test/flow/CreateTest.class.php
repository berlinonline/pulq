<?php

/**
  * @class User_CreateTest
  * Test basic function of the UserService
  *
  * @package UserService
  * @subpackage Test
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */
require_once(__DIR__ . '/UserFlowTestCase.class.php');
class User_CreateTest extends UserFlowTestCase
{
//     protected $backupGlobals = FALSE;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->contextName = 'web';
        $this->actionName = 'Create';
        $this->moduleName = 'User';
        $this->input = '/user/create/';
    }

    /**
      * A Password should have at least 8 chars
      */
    public function testShortPassword()
    {
        $parameters = array(
            'loginname' => 'testShortPassword',
            'email' => 'mathias.fischer@berlinonline.de',
            'password' => 'insecur',
        );
        $this->dispatch($parameters, 'json');
        $response = $this->response->getContent();
        $this->assertUserService_Status($response, 400);
    }

    /**
      * A valid mail address is required
      */
    public function testInvalidMail()
    {
        $parameters = array(
            'loginname' => 'testInvalidMail',
            'email' => 'mathias.fischer',
            'password' => 'security3',
        );
        $this->dispatch($parameters, 'json');
        $response = $this->response->getContent();
        $this->assertUserService_Status($response, 400);
    }

    /**
      * Test create user
      */
    public function testExecuteWrite()
    {
        $parameters = array(
            'loginname' => 'testExecuteWrite',
            'email' => 'mathias.fischer@berlinonline.de',
            'password' => 'security3',
        );
        $this->dispatch($parameters, 'json', 'Write');
        $this->assertEquals(201, $this->response->getHttpStatusCode());
        $context = $this->getContext();
        $response = $this->response->getContent();
        $this->assertUserService_User($response);
        #echo $response;


        try
        {
            $user = $this->db->fetchUserByLoginname('testExecuteWrite');
        }
        catch (Exception $exception)
        {
            $message = $this->handleUnknownException($exception);
            $this->assertTrue(FALSE, $message);
        }
        $this->assertInstanceOf("UserService_User_Interface", $user);
        $this->assertEquals('testExecuteWrite', $user->getLoginname());
        $this->db->deleteUser($user);
    }


    /**
      * Test create user on existing login
      */
    public function testDuplicate()
    {
        $context = $this->getContext();
        $user = new UserService_User();
        $user->setLoginname('testDuplicate');
        $this->db->replaceUser($user);

        $parameters = array(
            'loginname' => 'testDuplicate',
            'email' => 'mathias.fischer@berlinonline.de',
            'password' => 'security3',
        );
        $this->dispatch($parameters, 'json', 'Write');

        $response = $this->response->getContent();
        $this->assertUserService_Status($response, 409);

        $this->db->deleteUser($user);
    }


}

?>