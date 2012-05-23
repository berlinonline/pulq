<?php

/**
 * Exception class for ExtendedCouchDbClient exceptions
 *
 * Postive exception codes are CURL error codes.
 *
 * @see ExtendedCouchDbClient
 *
 * @version         $Id: CouchdbClientException.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          tay
 * @package Project
 * @subpackage Database/CouchDb
 */
class CouchdbClientException extends Exception
{
    /**
     * response from couch db server is not parseable
     */
    const UNPARSEABLE_RESPONSE = -1;

    /**
     * prepararing data for PUT requests failed
     */
    const PUT_DATA = -2;

}