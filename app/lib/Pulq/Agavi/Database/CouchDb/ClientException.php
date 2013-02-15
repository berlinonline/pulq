<?php

namespace Pulq\Agavi\Database\CouchDb;

/**
 * Exception class for CouchDb\Client exceptions
 *
 * Postive exception codes are CURL error codes.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          tay
 */
class ClientException extends \Exception
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
