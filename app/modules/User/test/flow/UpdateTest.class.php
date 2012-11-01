<?php

/**
  * @class User_UpdateTest
  * Test basic function of the UserService
  *
  * @package UserService
  * @subpackage Test
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */
require_once(__DIR__ . '/UserFlowTestCase.class.php');
class User_UpdateTest extends UserFlowTestCase
{
    //protected $runTestInSeparateProcess = FALSE;

    const LOGIN = 'testUpdate';
    const EMAIL = 'testUpdate@service.berlinonline.de';
    const PASS  = 'testUpdate1';


    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->contextName = 'web';
        $this->actionName = 'Update';
        $this->moduleName = 'User';
        $this->input = '/user/0/update/';
    }

    /**
      * Test create user
      */
    public function testUpdate()
    {
        //Do Request
        $contents = $this->user->toArray();
        $contents['realname'] = __METHOD__;
        $contents = json_encode($contents);
        $uploadedFile = new AgaviUploadedFile(array(
            'name' => 'POST',
            'type' => 'application/json',
            'size' => strlen($contents),
            'contents' => $contents,
            'error' => UPLOAD_ERR_OK,
            'is_uploaded_file' => false,
        ));
        $parameters = $this->createRequestDataHolder(array(
            AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array(
                'user_id' => $this->user->getId(),
            ),
            AgaviWebRequestDataHolder::SOURCE_FILES => array(
                'post_file' => $uploadedFile,
            ),
        ));

        $this->dispatch($parameters, 'json', 'Write');


        //Test Response
        $response = $this->response->getContent();
        $this->assertEquals(302, $this->response->getHttpStatusCode());
        $context = $this->getContext();
        $this->assertUserService_User($response);


        //Test DB
        try
        {
            $user_changed = $this->db->fetchUserById($this->user->getId());
        }
        catch (Exception $exception)
        {
            $message = $this->handleUnknownException($exception);
            $this->assertTrue(FALSE, $message);
        }
        $this->assertInstanceOf("UserService_User_Interface", $user_changed);
        $this->assertEquals('testUpdate', $user_changed->getLoginname());
        $this->assertEquals(__METHOD__, $user_changed->getName());
    }



}

?>