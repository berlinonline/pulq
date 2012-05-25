<?php

/**
 * The CouchDocumentStore is a couchdb specific implementation of the IDocumentStore interface.
 * It uses the ExtendedCouchDbClient to store, retrieve and delete documents from it's given database (client).
 *
 * @version $Id: CouchDocumentStore.class.php 1062 2012-03-30 15:11:59Z tschmitt $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Project
 * @subpackage DataObject
 */
class CouchDocumentStore implements IDocumentStore
{
    /**
     * The name of couchdb's internal id field.
     */
    const COUCH_ID = '_id';

    /**
     * The name of couchdb's internal revision field.
     */
    const COUCH_REV = '_rev';

    /**
     * The name of the field we store the document's type meta information in.
     * The type data is used by the factory method to determine the correct document implementor
     * and is added/removed transparently before data is stored/hydrated.
     * Carefull with the choice of name as you may overwrite document data,
     * if the document has a member with the same name.
     */
    const DOC_IMPLEMENTOR = 'type';

    /**
     * The name of the document's id field.
     */
    const DOC_IDENTIFIER = 'identifier';

    /**
     * The name of the document's revision field.
     */
    const DOC_REVISION = 'revision';

    /**
     * Holds the client that we use to connect to the couch database.
     *
     * @var ExtendedCouchDbClient
     */
    protected $client;

    /**
     * Create a new CouchDocumentStore instance.
     *
     * @param ExtendedCouchDbClient $client
     */
    public function __construct(ExtendedCouchDbClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new IDocument instance from the given data.
     * The passed $data array will be search for the self::DOC_IMPLEMENTOR field
     * and it's value used to determine the correct IDocument implementation to create.
     *
     * @param array $data The data to hydrate the new document with.
     *
     * @return IDocument A fresh IDocument instance initialized with the given data.
     *
     * @throws Exception
     */
    public static function factory(array $data = array())
    {
        // check if we have a required type-key set to lookup our implementor.
        if (! isset($data[self::DOC_IMPLEMENTOR]))
        {
            throw new Exception(
                "Unable to create document without type information within data.\n".print_r($data, TRUE)
            );
        }
        $docType = $data[self::DOC_IMPLEMENTOR];
        if (! class_exists($docType, TRUE))
        {
            throw new Exception("Unable to find document-type: " . $docType);
        }

        // Call the IDataObject's fromArray factory method on the resolved type to create a new document.
        $document = $docType::fromArray(
            self::prepareObjectData($data)
        );
        if (! ($document instanceof IDocument))
        {
            throw new Exception(
                "The given document-type is not an implmentation of IDocument."
            );
        }
        return $document;
    }

    /**
     * Delete the given document from the database.
     *
     * @param IDocument $document The document to delete.
     *
     * @return boolean Returns true if everything worked out, false otherwise.
     */
    public function delete(IDocument $document)
    {
        $result = $this->client->deleteDoc(
            NULL,
            $document->getIdentifier(),
            $document->getRevision()
        );
        return isset($result['ok']);
    }

    /**
     * Find a document by identifier.
     *
     * @param type $identifier The document identifier to look for.
     * @param type $revision The document revision to return.
     *
     * @return IDocument The document or null if there is no document for the given identifier.
     *
     * @throws CouchdbClientException
     */
    public function fetchByIdentifier($identifier, $revision = NULL)
    {
        try
        {
            return self::factory(
                $this->client->getDoc(NULL, $identifier, $revision)
            );
        }
        catch(CouchdbClientException $e)
        {
            if (preg_match('~(\(404\))~', $e->getMessage()))
            {
                // no document for the given id in our current database.
                return NULL;
            }
            else
            {
                throw $e;
            }
        }
    }

    /**
     * Save the given document to the database.
     *
     * @param IDocument $document The document to save.
     *
     * @return boolean True if everything worked out, false otherwise.
     */
    public function save(IDocument $document)
    {
        $data = self::prepareStorageData($document);
        $result = $this->client->storeDoc(NULL, $data);
        if (isset($result['ok']))
        {
            // update the document's revision with the new revision provided by couch.
            $document->setRevision($result['rev']);
            if (! isset($data[self::COUCH_ID]))
            {
                // if the document was new and didn't provide an own id,
                // propagate the id, that was generated by couch, back to the document.
                $document->setIdentifier($result['id']);
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Turn the given document into an array representation,
     * that can directly be passed to couchdb as is.
     * Basically this means mapping the document's id and rev fields,
     * to couch's id and rev fields and making sure that the self::DOC_IDENTIFIER
     * value is set correctly to reflect the current type.
     *
     * @param IDocument $document
     *
     * @return array
     */
    public static function prepareStorageData(IDocument $document)
    {
        $data = $document->toArray();
        $data[self::DOC_IMPLEMENTOR] = get_class($document);
        if (isset($data[self::DOC_IDENTIFIER]))
        {
            $data[self::COUCH_ID] = $data[self::DOC_IDENTIFIER];
        }
        unset($data[self::DOC_IDENTIFIER]);
        if (isset($data[self::DOC_REVISION]))
        {
            $data[self::COUCH_REV] = $data[self::DOC_REVISION];
        }
        unset($data[self::DOC_REVISION]);
        return $data;
    }

    /**
     * Turn the given (couchdb result)array into an array representation
     * that can directly be passed to an IDocument's fromArray method as is.
     * Basically this means mapping the couch's id and rev fields,
     * to the document's id and rev fields and making sure that the self::DOC_IDENTIFIER field is removed.
     *
     * @param array $data
     *
     * @return array
     */
    public static function prepareObjectData(array $data)
    {
        if (isset($data[self::COUCH_ID]))
        {
            $data[self::DOC_IDENTIFIER] = $data[self::COUCH_ID];
            unset($data[self::COUCH_ID]);
        }
        if (isset($data[self::COUCH_REV]))
        {
            $data[self::DOC_REVISION] = $data[self::COUCH_REV];
            unset($data[self::COUCH_REV]);
        }
        if (isset($data[self::DOC_IMPLEMENTOR]))
        {
            unset($data[self::DOC_IMPLEMENTOR]);
        }
        return $data;
    }
}

?>
