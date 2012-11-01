<?php


/**
  * @class UserFlowTestCase
  *
  *
  * @package
  * @subpackage
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */

require_once(__DIR__ . '/../../lib/UserService/Autoload.class.php');

abstract class UserFlowTestCase extends AgaviFlowTestCase
{

    /**
      * UserService_User_Interface $user
      */
    public $user = NULL;


    /**
      * UserService_Database_Interface $db
      */
    public $db = NULL;

    /**
      * Test-User properties
      */
    const LOGIN = 'na';
    const EMAIL = '';
    const PASS  = '';

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUpBeforeClass()
     */
    static public function setUpBeforeClass()
    {
        UserService_Autoload::register();
    }


    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     */
    protected function setUp()
    {
        $context = $this->getContext();

        $pdo = $context->getDatabaseConnection('UserServiceDB');
        $pdo->exec('TRUNCATE `xusergroup`');
        $pdo->exec('TRUNCATE `users`');
        $pdo->exec('TRUNCATE `groups');

        $this->db = new UserService_Database($pdo);
        if ($this::LOGIN !== 'na')
        {
            $user = new UserService_User();
            $user->setLoginname($this::LOGIN);
            $user->setEmail($this::EMAIL);
            $user->setPassword($this::PASS);
            $user = $this->db->replaceUser($user);
            $this->user = $user;

        }
    }


    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        if ($this::LOGIN !== 'na')
        {
            $this->db->deleteUser($this->user);
        }
    }

    /**
     * (non-PHPdoc)
     * @see AgaviFlowTestCase::dispatch()
     */
    public function dispatch($arguments = null, $outputType = null, $requestMethod = null)
    {
        parent::dispatch($arguments, ($outputType ? $outputType : 'json'), $requestMethod);
    }

    /**
      * Handle an unexpected exception, usually caught if the pdo-serialize()-bug appears
      * This function just builds the message to avoid a phpunit-bug
      *
      * @param Exception $exception
      * @return String Errormessage
      */
    protected function handleUnknownException (Exception $exception)
    {
        $message = $exception->getMessage()."\n";
        $message .= $exception->getTraceAsString();
        return 'Unknown '.get_class($exception).': '.$message;
    }



    /**
      * Check if response is of type json and contains the relevant status fields
      *
      * @param String $json Response
      * @param Int $status HTTP status code to check
      */
    public function assertUserService_Status ($json, $status)
    {
        $this->assertTrue('{' === $json{0}, "No JSON-Data << ".substr($json, 0, 256));
        $response = json_decode($json, TRUE);
        $this->assertTrue(is_array($response), 'JSON response must be a array');
        $this->assertArrayHasKey('code', $response, 'JSON response must contain a member "code"');
        $this->assertTrue($response['code'] == $status, "Invalid Status, expected $status != ".$response['code']);
    }


    /**
      * Check if response is of type json and contains the relevant fields for a user
      *
      * @param String $json Response
      */
    public function assertUserService_User ($json)
    {
        $this->assertTrue('{' === $json{0}, "No JSON-Data << ".$json);
        $response = json_decode($json, TRUE);
        $this->assertFalse(isset($response['ok']), "Invalid User, Status returned << ".$json);
        $this->assertTrue(isset($response['loginname']), "No Loginname in response << ".$json);
        $this->assertTrue(isset($response['email']), "No Email in response << ".$json);
        $this->assertTrue(isset($response['user_id']), "No User ID in response << ".$json);
    }


    /**
     *
     *
     * @return multitype:
     */
    public function __sleep()
    {
        $out = array();
        foreach (get_object_vars($this) as $name => $val)
        {
            if (is_resource($val))
            {
                continue;
            }
            if ($val instanceof UserService_Database_Interface)
            {
                continue;
            }
            $out[] = $name;
        }
        return array();
    }

}


?>