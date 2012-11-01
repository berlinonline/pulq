<?php

/**
  * @class User_ReadTest
  * Test basic function of the UserService
  *
  * @package UserService
  * @subpackage Test
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */
require_once(__DIR__ . '/UserFlowTestCase.class.php');
class User_ReadTest extends UserFlowTestCase
{
    //protected $runTestInSeparateProcess = FALSE;

    const LOGIN = 'testRead';
    const EMAIL = 'testRead@service.berlinonline.de';
    const PASS  = 'testRead1';


    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->contextName = 'web';
        $this->actionName = 'Read';
        $this->moduleName = 'User';
        $this->input = '/user/0/';
    }

    /**
      * ID has to be a number
      */
    public function testInvalidId()
    {
        $parameters = array(
            'user_id' => 'testInvalidId',
        );
        $this->dispatch($parameters, 'json');
        $response = $this->response->getContent();
        $this->assertUserService_Status($response, 400);
    }

    /**
      * Fetch an existing User
      */
    public function testExecuteRead()
    {
        $parameters = array(
            'user_id' => $this->user->getId(),
        );
        $this->dispatch($parameters, NULL, 'Read');
        $this->assertEquals(200, $this->response->getHttpStatusCode());
        $response = $this->response->getContent();
        $this->assertUserService_User($response);
    }

    /**
      * Fetch a non-existing User
      */
    public function testMissed()
    {
        $parameters = array(
            'user_id' => 999999999,
        );
        $this->dispatch($parameters, NULL, 'Read');
        $this->assertEquals(404, $this->response->getHttpStatusCode());
        $response = $this->response->getContent();
        $this->assertUserService_Status($response, 404);
    }


}

?>