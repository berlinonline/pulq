<?php

namespace Pulq\Agavi\Database\CouchDb;

/**
 * The CouchDb\Client wraps a selection of couchdb api calls to php using CURL
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @author          Tom Anheyer <tanheyer@gmail.com>
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class Client
{
    /**
     * default couch db host ist localhost
     */
    const DEFAULT_HOST = 'localhost';

    /**
     * default couch database port is 5984
     */
    const DEFAULT_PORT = '5984';

    /**
     * default couch database connect url
     */
    const DEFAULT_URL = 'http://localhost:5984';

    /**
     * HTTP request method GET
     */
    const METHOD_GET = 'GET';
    /**
     * HTTP request method PUT
     */
    const METHOD_PUT = 'PUT';
    /**
     * HTTP request method POST
     */
    const METHOD_POST = 'POST';
    /**
     * HTTP request method DELETE
     */
    const METHOD_DELETE = 'DELETE';
    /**
     * HTTP request method HEAD
     */
    const METHOD_HEAD = 'HEAD';

    /**
     * Request completed successfully.
     * @see http://wiki.apache.org/couchdb/HTTP_status_list
     */
    const STATUS_OK = 200;
    /**
     * Document created successfully.
     * @see http://wiki.apache.org/couchdb/HTTP_status_list
     */
    const STATUS_CREATED = 201;
    /**
     * Request for database compaction completed successfully.
     * @see http://wiki.apache.org/couchdb/HTTP_status_list
     */
    const STATUS_ACCEPTED = 202;
    /**
     * Etag not modified since last update.
     * @see http://wiki.apache.org/couchdb/HTTP_status_list
     */
    const STATUS_NOT_MODIFIED = 304;
    /**
     * Request given is not valid in some way.
     * @see http://wiki.apache.org/couchdb/HTTP_status_list
     */
    const STATUS_BAD_REQUEST = 400;
    /**
     * Such as a request via the HttpDocumentApi for a document which doesn't exist.
     * @see http://wiki.apache.org/couchdb/HTTP_status_list
     */
    const STATUS_NOT_FOUND = 404;
    /**
     * Request was accessing a non-existent URL. For example, if you have a malformed URL,
     * or are using a third party library that is targeting a different version of CouchDB.
     *
     * @see http://wiki.apache.org/couchdb/HTTP_status_list
     */
    const STATUS_RESOURCE_NOT_ALLOWED = 405;
    /**
     * Request resulted in an update conflict.
     * @see http://wiki.apache.org/couchdb/HTTP_status_list
     */
    const STATUS_CONFLICT = 409;
    /**
     * Request attempted to created database which already exists.
     * @see http://wiki.apache.org/couchdb/HTTP_status_list
     */
    const STATUS_PRECONDITION_FAILED = 412;
    /**
     * Request contained invalid JSON, probably happens in other cases too.
     * @see http://wiki.apache.org/couchdb/HTTP_status_list
     */
    const STATUS_INTERNAL_SERVER_ERROR = 500;

    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds base uri used to talk to couch db.
     *
     * @var         string
     */
    protected $baseUri;

    /**
     * Name of default database to use
     * @var         string
     */
    protected $defaultDatabase;

    /**
     * Holds a curl handle that is internally used for submitting requests.
     *
     * @var         Resource
     */
    private $curlHandle = NULL;

    /**
     * holds filename of cookie file used for session auth
     */
    private $cookieFile;

    /**
     * holds the last used url for internal error reporting
     * @var string
     */
    private $lastUri;

    /**
     * holds the last used method for internal error reporting
     * @var unknown_type
     */
    private $lastMethod;

    /**
     * holds the last raw response for internal error reporting
     * @var string
     */
    private $lastResponse;

    /**
     *
     * @var array of curl options to feed curl_setopt_array
     */
    protected $curlOptions;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------

    /**
     * Create a new ExtendedCouchDbClient instance passing in the couchdb base uri.
     *
     * @param       string $uri URL to couchdb server
     * @param       string $database default database name to use
     * @param       array $options optional options for curl, keys must be the names of curl setopt constants
     */
    public function __construct($uri, $database = NULL, array $options = NULL)
    {
        if ('/' != substr($uri, -1, 1))
        {
            $uri .= '/';
        }
        $this->baseUri = $uri;
        $this->defaultDatabase = $database;

        $headers = array(
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json',
            'Connection: keep-alive',
            'Expect:'
        );

        $this->curlOptions = array(
            CURLOPT_VERBOSE => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_FORBID_REUSE => 0,
            CURLOPT_FRESH_CONNECT => 0,
            CURLOPT_FOLLOWLOCATION => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_PROXY => '',
            CURLOPT_FAILONERROR => 0,
            CURLOPT_HTTPHEADER => $headers,
        );

        if (is_array($options))
        {
            foreach ($options as $key => $val)
            {
                if (defined($key))
                {
                    $this->curlOptions[constant($key)] = $val;
                }
            }
        }
    }


    /**
     * close system resources
     */
    public function __destruct()
    {
        if (is_resource($this->curlHandle))
        {
            curl_close($this->curlHandle);
            $this->curlHandle = NULL;
        }
        if ($this->cookieFile)
        {
            unlink($this->cookieFile);
        }

    }

    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * gets our default database name
     *
     * The default database name is used by all methods if no database name is given for the database argument.
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->defaultDatabase;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }


    /**
     * open a user session and login
     *
     * @param string $user user account name
     * @param string $password user password
     * @throws ClientException on protocol errors
     * @return mixed TRUE on login success or error message
     */
    public function login($user, $password)
    {
        $uri = $this->baseUri.'_session';

        // cookie management only necessary for session auth
        $this->cookieFile = tempnam(AgaviConfig::get('core.cache_dir'), get_class($this).'_');
        $this->curlOptions[CURLOPT_COOKIEFILE] = $this->cookieFile;
        $this->curlOptions[CURLOPT_COOKIEJAR] = $this->cookieFile;

        $curlHandle = $this->getCurlHandle($uri);
        curl_setopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curlHandle, CURLOPT_USERPWD, $user.':'.$password);

        $data = $this->getJsonData($curlHandle);

        return isset($data['error'])
            ? $data['error']
            : isset($data['ok']) && $data['ok']
                ? TRUE
                : 'General Gaddafi';
    }


    /**
     * Send a batch create/update request to the given couch database
     * and return the resulting response information.
     *
     * @see http://wiki.apache.org/couchdb/HTTP_Bulk_Document_API#Modify_Multiple_Documents_With_a_Single_Request
     *
     * @param       string $database
     * @param       array $documentData
     * @param       boolean $allOrNothing
     *
     * @return      array
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function storeDocs($database, array $documentData, $allOrNothing = FALSE)
    {
        foreach ($documentData as $key => & $doc)
        {
            if (array_key_exists('_id', $doc))
            {
                $doc['_id'] = (string)$doc['_id'];
            }
        }

        $data = ($allOrNothing)
            ? array('all_or_nothing' => TRUE, 'docs' => $documentData)
            : array('docs' => $documentData);

        $uri = $this->getDatabaseUrl($database).'_bulk_docs';
        $curlHandle = $this->getCurlHandle($uri, self::METHOD_POST);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, json_encode($data));
        $data = $this->getJsonData($curlHandle, self::STATUS_CONFLICT);
        return $data;
    }

    /**
     * Fetch the data for the given $documentId for the passed $database.
     *
     * @param       string $database
     * @param       string $documentId
     * @param       string $revision optional revision id if not accessing the head revision
     *
     * @return      array
     *
     * @throws      ClientException
     */
    public function getDoc($database, $documentId, $revision = NULL)
    {
        $uri = $this->getDatabaseUrl($database).urlencode($documentId);
        if (NULL !== $revision)
        {
            $uri .= '?'.http_build_query(array('rev' => $revision));
        }
        $curlHandle = $this->getCurlHandle($uri);
        return $this->getJsonData($curlHandle);
    }

    /**
     * Fetch all documents from the given database.
     *
     * @see http://wiki.apache.org/couchdb/HTTP_Bulk_Document_API#Fetch_Multiple_Documents_With_a_Single_Request
     * @uses _getView()
     *
     * @param       string $database
     * @param       array $parameters parameters for the _all_docs api call (include_docs, start_key, end_key, …)
     * @param       array $keys list of document ids to lookup
     *
     * @return      array with view result
     *
     * @throws      ClientException
     */
    public function getAllDocs($database, array $parameters = NULL, array $keys = NULL)
    {
        $url = $this->getDatabaseUrl($database).'_all_docs';
        return $this->_getView($url, $parameters, $keys);
    }

    /**
     * Create or update document in the given database.
     *
     * @see http://wiki.apache.org/couchdb/HTTP_Document_API#PUT
     *
     * @param       string $database
     * @param       array $document assoziative array with document data
     *
     * @return      array
     *
     * @throws      ClientException
     */
    public function storeDoc($database, array $document)
    {
        if (empty($document['_id']))
        {
            return $this->storeDocAutoId($database, $document);
        }
        return $this->putData(
            $this->getDatabaseUrl($database).urlencode($document['_id']),
            $document,
            self::STATUS_CONFLICT);
    }


    /**
     * Store new document using the POST api method
     *
     * @see http://wiki.apache.org/couchdb/HTTP_Document_API#POST
     *
     * @param       string $database
     * @param       array $document
     * @throws      ClientException
     * @return      array
     */
    public function storeDocAutoId($database, array $document)
    {
        $curlHandle = $this->getCurlHandle($this->getDatabaseUrl($database), self::METHOD_POST);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $this->encodeDocumentToJson($document));
        $data = $this->getJsonData($curlHandle, self::STATUS_CONFLICT);
        return $data;
    }

    public function nextUuids($count = 1)
    {
        $url = $this->baseUri . '_uuids?count='.(int)$count;
        $curlHandle = $this->getCurlHandle($url, self::METHOD_GET);
        $uuidData = $this->getJsonData($curlHandle);

        return $uuidData['uuids'];
    }

    /**
     * Delete a document from the given couchdb database by id and revision.
     *
     * @see http://wiki.apache.org/couchdb/HTTP_Document_API#DELETE
     * @throws ClientException on protocol errors
     *
     * @param       string $database
     * @param       string $docId
     * @param       string $revision
     *
     * @return      boolean
     */
    public function deleteDoc($database, $docId, $revision)
    {
        $uri = $this->getDatabaseUrl($database) . urlencode($docId) . '?' . 'rev=' . urlencode($revision);
        $curlHandle = $this->getCurlHandle($uri, self::METHOD_DELETE);
        $data = $this->getJsonData($curlHandle, self::STATUS_NOT_FOUND);
        return (isset($data['ok']) && TRUE === $data['ok']);
    }

    /**
     * Query the given database for revision (e-tag header in response to head request)
     * information on the given $docId.
     * If the document does not exist 0 is returned.
     *
     * @see http://wiki.apache.org/couchdb/HTTP_Document_API#HEAD
     *
     * @param       string $database
     * @param       string $docId
     *
     * @return      int Returns the document's revision or 0 if it doesn't exist.
     */
    public function statDoc($database, $docId)
    {
        $uri = $this->getDatabaseUrl($database) . urlencode($docId);
        $curlHandle = $this->getCurlHandle($uri, self::METHOD_HEAD);
        $resp = curl_exec($curlHandle);
        $this->processCurlErrors($curlHandle, self::STATUS_NOT_FOUND);

        if (404 == curl_getinfo($curlHandle, CURLINFO_HTTP_CODE))
        {
            return 0;
        }

        if (preg_match_all('~Etag:\s*"(\d+-\w+)"~is', $resp, $matches, PREG_SET_ORDER))
        {
            return $matches[0][1];
        }

        return 0;
    }

    /**
     * Fetch the data for the given view.
     *
     * @see http://wiki.apache.org/couchdb/HTTP_view_API#Access.2BAC8-Query
     *
     * Resulting array looks like
     * <pre>
     * Array
     *   (
     *       [total_rows] => 1
     *       [offset] => 0
     *       [rows] => Array
     *           (
     *               [0] => Array
     *                   (
     *                      …
     *                      [id] => …
     *                      [[doc] => …]
     *                   )
     *           )
     *   )
     * </pre>
     *
     * @uses _getView()
     *
     *
     * @param       string $database name of database to use
     * @param       string $designDocId design document identifier
     * @param       string $viewname
     * @param       array $parameters addition view query parameters as described in the couchdb api documentation
     * @param       array $keys optional list of document ids to lookup
     *
     * @throws      ClientException
     *
     * @return      array result from view
     */
    public function getView($database, $designDocId, $viewname, array $parameters = array(), array $keys = NULL)
    {
        $url = $this->getDatabaseUrl($database) .
            '_design/' . urlencode($designDocId) .
            '/_view/' . urlencode($viewname);
        return $this->_getView($url, $parameters, $keys);
    }

    /**
     * Create a design document for the given $docId.
     *
     * @param       string $database
     * @param       string $docId
     * @param       array $doc
     *
     * @return type
     */
    public function createDesignDocument($database, $docId, array $doc)
    {
        $doc['_id'] = $docId;
        $uri = $this->getDatabaseUrl($database) . '/_design/' . urlencode($docId);
        return $this->putData($uri, $doc, self::STATUS_CONFLICT);
    }


    /**
     * get infomation about a design document
     *
     * @see http://wiki.apache.org/couchdb/Complete_HTTP_API_Reference#Special_design_documents
     *
     * @param string $database
     * @param integer $docid
     * @throws ClientException on protocol errors or result is not json
     * @return mixed array or boolean FALSE
     */
    public function getDesignDocument($database, $docid)
    {
        $uri = $this->getDatabaseUrl($database).'_design/'.urlencode($docid);
        $curlHandle = $this->getCurlHandle($uri);
        $result = $this->getJsonData($curlHandle, self::STATUS_NOT_FOUND);
        return isset($result['error']) ? FALSE : $result;
    }

    /**
     * get database information
     *
     * returned array contains
     * <ul>
     * <li>db_name - Name of the database (string)
     * <li>doc_count - Number of documents (including design documents) in the database (int)
     * <li>update_seq - Current number of updates to the database (int)
     * <li>purge_seq - Number of purge operations (int)
     * <li>compact_running - Indicates, if a compaction is running (boolean)
     * <li>disk_size - Current size in Bytes of the database (Note: Size of views indexes on disk are not included)
     * <li>instance_start_time - Timestamp of CouchDBs start time (int in ms)
     * <li>disk_format_version - Current version of the internal database format on disk (int)
     * </ul>
     *
     * @see http://wiki.apache.org/couchdb/HTTP_database_API#Database_Information
     *
     * @throws ClientException on protocol errors or result is not json
     * @param string $database
     *
     * @return mixed array or boolean FALSE
     */
    public function getDatabase($database)
    {
        $uri = $this->getDatabaseUrl($database);
        $curlHandle = $this->getCurlHandle($uri);
        $result = $this->getJsonData($curlHandle, self::STATUS_NOT_FOUND);
        return isset($result['error']) ? FALSE : $result;
    }

    /**
     * Create a database by the given $database name.
     *
     * Couch responses:
     *
     * Database successfully created
     * <ul>
     * <li>HTTP 201
     * <li>Json: {"ok":true}
     * </ul>
     *
     * Database exists
     * <ul>
     * <li>HTTP 412
     * <li>{"error":"file_exists","reason":"The database could not be created, the file already exists."}
     * </ul>
     *
     * @see http://wiki.apache.org/couchdb/HTTP_database_API#PUT_.28Create_New_Database.29
     *
     * @param string $database name of new database
     * @return boolean TRUE on database created, FALSE on database already exists
     * @throws ClientException on protocol errors, access denied, etc.
     */
    public function createDatabase($database = NULL)
    {
        $curlHandle = $this->getCurlHandle($this->getDatabaseUrl($database), self::METHOD_PUT);
        $data = $this->getJsonData($curlHandle, self::STATUS_PRECONDITION_FAILED);
        return isset($data['ok']);
    }

    /**
     * Delete a database by the given $database name.
     *
     * Database successfully delete
     * <ul>
     * <li>HTTP 201
     * <li>Json: {"ok":true}
     * </ul>
     *
     * Database exists
     * <ul>
     * <li>HTTP 440
     * <li>{"error":"not_found","reason":"missing"}
     * </ul>
     *
     * @see http://wiki.apache.org/couchdb/HTTP_database_API#PUT_.28Create_New_Database.29
     *
     * @param string $database name of existing database
     *
     * @return boolean TRUE on database found and deleted, FALSE on database missing
     *
     * @throws ClientException on protocol errors, access denied, etc.
     */
    public function deleteDatabase($database)
    {
        $curlHandle = $this->getCurlHandle($this->getDatabaseUrl($database), self::METHOD_DELETE);
        $data = $this->getJsonData($curlHandle, self::STATUS_NOT_FOUND);
        return isset($data['ok']);
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Returns our curl handle and initializes it upon first invocation.
     *
     * @param       string $uri complete url to couchdb object/request
     * @param       string $method http method to use for this request
     * @return      Resource
     */
    protected function getCurlHandle($uri, $method = self::METHOD_GET)
    {
        $this->lastUri = $uri;
        $this->lastMethod = $method;
        $this->lastResponse = NULL;

        if (TRUE || ! is_resource($this->curlHandle))
        {
            $curlHandle = $this->curlHandle = curl_init($uri);
        }
        else
        {
            $curlHandle = $this->curlHandle;
            // reset existing handle
            curl_setopt_array($curlHandle, array(
                CURLOPT_URL => $uri,
                CURLOPT_HEADER => 0,
                CURLOPT_HTTPGET => 1,
            ));
        }
        curl_setopt_array($curlHandle, $this->curlOptions);

        switch ($method)
        {
            case self::METHOD_GET:
                curl_setopt($curlHandle, CURLOPT_HTTPGET, 1);
                break;
            case self::METHOD_HEAD:
                curl_setopt($curlHandle, CURLOPT_HTTPGET, 1);
                curl_setopt($curlHandle, CURLOPT_NOBODY, 1);
                curl_setopt($curlHandle, CURLOPT_HEADER, 1);
                break;
            case self::METHOD_POST:
                curl_setopt($curlHandle, CURLOPT_POST, 1);
                break;
            case self::METHOD_PUT:
                curl_setopt($curlHandle, CURLOPT_PUT, 1);
                break;
            default:
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $method);
                break;
        }

        return $curlHandle;
    }

    /**
     * Check curl response status and throw exception on error.
     *
     * @param       Resource $curlHandle
     * @param       integer expected alternative or method specific HTTP status code.
     *                      Codes between 200 <= x < 400 are allways OK
     *
     * @return      void
     *
     * @throws      ClientException
     */
    protected function processCurlErrors($curlHandle, $validReturnCode = self::STATUS_OK)
    {
        $respCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        if ((self::STATUS_OK > $respCode || $respCode >= self::STATUS_BAD_REQUEST) && $validReturnCode != $respCode)
        {
            $data = json_decode($this->lastResponse, TRUE);
            if (empty($data['error']))
            {
                $error = curl_error($curlHandle);
            }
            else
            {
                $error = $data['error'] .
                    (empty($data['reason']) ? '' : ' :: '.$data['reason']);
            }
            $errorNum = curl_errno($curlHandle);
            throw new ClientException(
                $this->lastUri." ($respCode): $error", $errorNum);
        }
    }

    /**
     * execute http request and decode json response to php array
     *
     * @param resource $curlHandle
     * @param integer $validReturnCode
     * @throws ClientException
     * @return array
     */
    protected function getJsonData($curlHandle, $validReturnCode = self::STATUS_OK)
    {
        $this->lastResponse = curl_exec($curlHandle);
        $this->processCurlErrors($curlHandle, $validReturnCode);
        $data = json_decode($this->lastResponse, TRUE);
        if (NULL === $data)
        {
            throw new ClientException(
                $this->lastUri.': Response body can not be parsed to JSON: '. $this->lastResponse,
                ClientException::UNPARSEABLE_RESPONSE);
        }
        return $data;
    }

    /**
     * encode document data array as JSON
     *
     * @param array $document
     * @return array
     */
    protected function encodeDocumentToJson(array $document)
    {
        if (array_key_exists('_id', $document))
        {
            $document['_id'] = (string)$document['_id'];
        }
        return json_encode($document);
    }


    /**
     * execute a PUT request to the database
     *
     * @param string $uri complete api URL
     * @param array $document
     * @param int $validReturnCode
     * @throws ClientException
     * @return array
     */
    protected function putData($uri, array $document, $validReturnCode)
    {
        $curlHandle = $this->getCurlHandle($uri, self::METHOD_PUT);

        $body = $this->encodeDocumentToJson($document);
        $docFd = fopen('data://text/plain,'.urlencode($body), 'r');
        if (! $docFd)
        {
            throw new ClientException('Can not setup PUT data.', ClientException::PUT_DATA);
        }
        curl_setopt($curlHandle, CURLOPT_INFILE, $docFd);
        curl_setopt($curlHandle, CURLOPT_INFILESIZE, strlen($body));
        $data = $this->getJsonData($curlHandle, $validReturnCode);
        fclose($docFd);
        return $data;
    }

    /**
     * get the url to current database
     *
     * @param string $database optional database name; defaults to database name given in constructor
     * @return string
     */
    protected function getDatabaseUrl($database = NULL)
    {
        return $this->baseUri.urlencode(empty($database) ? $this->defaultDatabase : $database).'/';
    }

    /**
     * fix and add parameters to (view) URL
     *
     * @param string $url
     * @param array $parameters
     *
     * @return string extended url
     */
    protected function addUrlParameters($url, array $parameters)
    {
        foreach ($parameters as $key => & $value)
        {
            if ('key' == $key || 'startkey' == $key || 'endkey' == $key)
            {
                $value = json_encode($value);
            }
            else if (is_bool($value))
            {
                $value = $value ? 'true' : 'false';
            }
            else if (is_array($value))
            {
                $value = json_encode($value);
            }
        }
        return $url.'?'.http_build_query($parameters);
    }

    /**
     * internal function for accessing views
     *
     * @see http://wiki.apache.org/couchdb/HTTP_view_API#Access.2BAC8-Query
     *
     * Resulting array looks like
     * <pre>
     * Array
     *   (
     *       [total_rows] => 1
     *       [offset] => 0
     *       [rows] => Array
     *           (
     *               [0] => Array
     *                   (
     *                      …
     *                      [id] => …
     *                      [[doc] => …]
     *                   )
     *           )
     *   )
     * </pre>
     *
     * @see getView()
     * @see getAllDocs()
     *
     * @param string $url
     * @param array $parameters parameters for the _all_docs api call (include_docs, start_key, end_key, …)
     * @param array $keys list of document ids to lookup
     *
     * @return array result from view
     *
     * @throws ClientException
     */
    private function _getView($url, array $parameters = array(), array $keys = NULL)
    {
        $uri = $this->addUrlParameters($url, $parameters);

        if (NULL === $keys)
        {
            $curlHandle = $this->getCurlHandle($uri, self::METHOD_GET);
        }
        else
        {
            $kreq = array('keys' => array_values($keys));
            $curlHandle = $this->getCurlHandle($uri, self::METHOD_POST);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, json_encode($kreq));
        }

        $data = $this->getJsonData($curlHandle);

        return $data;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}
