<?php

require_once(__DIR__ . '/../../lib/UserService/Autoload.class.php');

/**
 *
 *
 * @author tay
 * @version $Id:$
 * @since 30.10.2012
 *
 */
class UserserviceTestSuite extends AgaviTestSuite
{
    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestSuite::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        UserService_Autoload::register();
    }
}