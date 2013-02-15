<?php

namespace Pulq\Agavi\Database;

/**
 * The IDatabaseSetup interface is responseable for setting up databases for usage.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer
 */
interface IDatabaseSetup
{
    /**
     * Setup everything required to provide the functionality exposed by our module.
     * In this case setup a couchdb database and view for our asset idsequence.
     *
     * @param       AgaviDatabase $database
     * @param       boolean $tearDownFirst
     */
    public function execute(\AgaviDatabase $database, $tearDownFirst = FALSE);
}
