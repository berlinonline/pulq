<?php

/**
  * @class User_LoginTest
  * Test basic function of the UserService
  *
  * @package UserService
  * @subpackage Test
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */
require_once(__DIR__ . '/UserFlowTestCase.class.php');
class User_LoginTest extends UserFlowTestCase
{
    #protected $runTestInSeparateProcess = FALSE;

    const LOGIN = 'testLogin';
    const EMAIL = 'testLogin@service.berlinonline.de';
    const PASS  = 'testLogin1';


    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->contextName = 'web';
        $this->actionName = 'Login';
        $this->moduleName = 'User';
        $this->input = '/user/login/';
    }

    /**
      * Fetch a non-existing User
      */
    public function testMissed()
    {
        $parameters = array(
            'auth' => 999999999999,
            'password' => $this::PASS,
        );
        $this->dispatch($parameters, 'json', 'Write');
        $this->assertEquals(404, $this->response->getHttpStatusCode());
        $response = $this->response->getContent();
        $this->assertUserService_Status($response, 404);
    }

    /**
      * Fetch an existing User
      */
    public function testLoginname()
    {
        $parameters = array(
            'auth' => $this::LOGIN,
            'password' => $this::PASS,
        );
        $this->dispatch($parameters, 'json', 'Write');
        $this->assertEquals(200, $this->response->getHttpStatusCode());
        $response = $this->response->getContent();
        $this->assertUserService_User($response);


    }

    /**
      * ID has to be a number
      */
    public function testMissedLogin()
    {
        $parameters = array(
            'auth' => 'testInvalidLogin',
            'password' => $this::PASS,
        );
        $this->dispatch($parameters, 'json', 'Write');
        $response = $this->response->getContent();
        $this->assertUserService_Status($response, 404);
    }



}

?>
