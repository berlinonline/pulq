<?php

require_once(__DIR__ . '/GeoAbstractUnitTest.class.php');

/**
 *
 *
 * @author tay
 * @since 06.12.2012
 *
 */
class AddressParserTest extends GeoAbstractUnitTest
{
    const fixture =
        'Müntefering (65) aus Frankfurt verursacht einen Schaden von 10178 auf dem Heimweg 55 durch die Bölschestraße 27 in 12587 Berlin.';
    const street = 'Bölschestraße';
    const house = '27';
    const zip = '12587';
    const city = 'Berlin';

    /**
     *
     */
    public function testDetectStreet()
    {
        $result = AddressParser::extractStreet(self::fixture);
        self::assertEquals('Bölschestraße', $result);
    }

    /**
     *
     */
    public function testDetectHouse()
    {
        $result = AddressParser::extractHouse(self::fixture, self::street);
        self::assertEquals(self::house, $result);
    }

    /**
     *
     */
    public function testDetectZip()
    {
        $result = AddressParser::extractZip(self::fixture, self::street);
        self::assertEquals(self::zip, $result);
    }

    /**
     *
     */
    public function testDetectCity()
    {
        $result = AddressParser::extractCity(self::fixture, self::street);
        self::assertEquals(self::city, $result);
    }

    /**
     *
     */
    public function testParse()
    {
        $result = AddressParser::parse(self::fixture);
        self::assertEquals(self::street, $result['street']);
        self::assertEquals(self::house, $result['house']);
        self::assertEquals(self::zip, $result['postal']);
        self::assertEquals(self::city, $result['city']);
    }
}
?>