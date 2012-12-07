<?php
/**
 *
 *
 * @author tay
 * @since 05.12.2012
 *
 */
abstract class GeoAbstractUnitTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        parent::setUp();
        $context = AgaviContext::getInstance();
        $context->getController()->initializeModule('Geo');
    }
}
?>
