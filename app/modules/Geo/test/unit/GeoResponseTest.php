<?php

require_once(__DIR__ . '/GeoAbstractUnitTest.class.php');

/**
 *
 *
 * @author tay
 * @since 06.12.2012
 *
 */
class GeoResponseTest extends GeoAbstractUnitTest
{
    /**
     *
     */
    public function testInvalidFieldName()
    {
        $this->setExpectedException('GeoException', NULL, GeoException::INVALID_RESULT_SECTION);
        $res = GeoResponse::getInstanceForApi();
        $res->setValue('address.test', 'value');
    }

    /**
     *
     */
    public function testSetValueOk()
    {
        $res = GeoResponse::getInstanceForApi();
        self::assertTrue($res->setValue('meta.source', 'test'));
    }

    /**
     *
     */
    public function testSetValueFail()
    {
        $this->setExpectedException('GeoException', NULL, GeoException::INVALID_RESULT_VALUE);
        $res = GeoResponse::getInstanceForApi();
        $res->setValue('location.wgs84', 'no coord');
    }


    /**
     *
     */
    public function testSetValueLocationOk()
    {
        $res = GeoResponse::getInstanceForApi();
        self::assertTrue(
            $res->setValue('location.wgs84',
                    array(
                        'lon' => 13.56815, 'lat' => 52.46654
                    )));
    }

    /**
     *
     */
    public function testSetValueNullOk()
    {
        $res = GeoResponse::getInstanceForApi();
        self::assertTrue($res->setValue('address.state', NULL));
    }


}
?>