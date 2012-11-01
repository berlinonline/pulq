<?php

/**
  * @class User_GetLockTest
  * Test basic function of the UserService
  *
  * @package UserService
  * @subpackage Test
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */
require_once(__DIR__ . '/UserFlowTestCase.class.php');
class User_LockTest extends UserFlowTestCase
{
    protected $runTestInSeparateProcess = FALSE;

    const LOGIN = 'testLock';
    const EMAIL = 'testLock@service.berlinonline.de';
    const PASS  = 'testLock1';


    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->contextName = 'web';
        $this->actionName = 'Lock';
        $this->moduleName = 'User';
        $this->input = '/user/0/lock/';
    }

    /**
      * Test create user
      */
    public function testGetLock()
    {
        $this->user->createLock(10);
        $user = $this->db->replaceUser($this->user);

        //Do Request
        $parameters = array(
            'user_id' => $user->getId(),
        );
        $this->dispatch($parameters, 'json', 'Read');

        //Test Response
        $response = $this->response->getContent();
        $this->assertEquals(200, $this->response->getHttpStatusCode());
        $expires = $this->response->getHttpHeader('Expires');
        $expires = implode('; ', $expires); //getHttpHeader returns an array
        $this->assertEquals($user->getExpiresTime(), $expires);
        $this->assertUserService_User($response);
    }



}

?>