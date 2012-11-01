<?php

/**
  * @class User_DeleteTest
  * Test basic function of the UserService
  *
  * @package UserService
  * @subpackage Test
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */
require_once(__DIR__ . '/UserFlowTestCase.class.php');
class User_DeleteTest extends UserFlowTestCase
{
    //protected $runTestInSeparateProcess = FALSE;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->contextName = 'web';
        $this->actionName = 'Delete';
        $this->moduleName = 'User';
        $this->input = '/user/0/delete/';
    }

    /**
      * Test create user
      */
    public function testDelete()
    {
        //Create User
        $context = $this->getContext();
        $db = new UserService_Database($context->getDatabaseManager()->getDatabase()->getConnection());
        $user = new UserService_User();
        $user->setLoginname('testDelete');
        $user->setEmail('mathias.fischer@berlinonline.de');
        $user = $db->replaceUser($user);

        //Do Request
        $parameters = array(
            'user_id' => $user->getId(),
        );
        $this->dispatch($parameters, 'json', 'Write');

        //Test Response
        $response = $this->response->getContent();
        $this->assertEquals(200, $this->response->getHttpStatusCode());
        $this->assertUserService_Status($response, 200);

        //Test DB
        $status = $db->existsId($user->getId());
        $this->assertFalse($status, "Deleted user still exists in DB");
    }



}

?>