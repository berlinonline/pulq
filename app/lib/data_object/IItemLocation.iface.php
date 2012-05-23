<?php

/**
 * The IItemLocation interface defines the requirements towards class implementations that would like to provide
 * location information.
 *
 * @version $Id: IItemLocation.iface.php 1111 2012-04-26 15:09:36Z tschmitt $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Project
 * @subpackage DataObject
 */
interface IItemLocation extends IDataObject
{
    /**
     * Returns an array holding the location's longitude and latitude.
     *
     * <pre>
     * Example value structure:
     * array(
     *     'lon' => 12.345,
     *     'lat'  => 23.456
     * )
     * </pre>
     *
     * @return array
     */
    public function getCoordinates();

    /**
     * Returns the location's city (berlin ...).
     *
     * @return string
     */
    public function getCity();

    /**
     * Returns the location's postal code.
     *
     * @return string
     */
    public function getPostalCode();

    /**
     * Returns the locations administrative district (pankow, mitte ...).
     *
     * @return string
     */
    public function getAdministrativeDistrict();

    /**
     * Returns the locations district (prenzlauer berg, wedding ...)
     *
     * @return string
     */
    public function getDistrict();

    /**
     * Returns the location's neighborhood (sprengel kiez, niederschönhausen).
     *
     * @return string
     */
    public function getNeighborHood();

    /**
     * Returns the location's street.
     *
     * @return string
     */
    public function getStreet();

    /**
     * Returns the location's housenumber.
     *
     * @return string
     */
    public function getHouseNumber();

    /**
     * Returns the location's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Return the location's details.
     * 
     * @return string
     */
    public function getDetails();

    /**
     * Returns the location's relevance
     *
     * @return int
     */
    public function getRelevance();
}

?>
