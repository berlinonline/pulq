<?php

/**
 * The IDatabaseSetup interface is responseable for setting up databases for usage.
 *
 * @version         $Id: IDatabaseSetup.iface.php 1010 2012-03-02 20:08:23Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer
 * @package Bobase
 * @subpackage Agavi/Database
 */
interface IDatabaseSetup
{
    /**
     * Setup everything required to provide the functionality exposed by our module.
     * In this case setup a couchdb database and view for our asset idsequence.
     *
     * @param       boolean $tearDownFirst
     */
    public function setup($tearDownFirst = FALSE);
}