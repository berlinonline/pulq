<?php

/**
 * The IDocumentStore is reponseable for storing, retrieving and deleting documents
 * to or from a specific media.
 * It's main goal is to provide write access and simple identifier based retrieval.
 * A storage implementation may add in one or two extra retrieval methods,
 * but is not meant to be a finder like object.
 * The interface is quite coarse grained and therefore flexible to implement,
 * which should make it easy to integrate different storage types.
 *
 * @version $Id: IDocumentStore.iface.php 1001 2012-03-02 14:33:43Z tschmitt $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Project
 * @subpackage DataObject
 */
interface IDocumentStore
{
    /**
     * Create a new IDocument instance from the given data.
     *
     * @return IDocument
     */
    static public function factory(array $data = array());

    /**
     * Save the given document to the storage.
     *
     * @param IDocument $document The document to save.
     *
     * @return boolean True if everything worked out, false otherwise.
     */
    public function save(IDocument $document);

    /**
     * Delete the given document from the storage.
     *
     * @param IDocument $document The document to delete.
     *
     * @return boolean Returns true if everything worked out, false otherwise.
     */
    public function delete(IDocument $document);

    /**
     * Retrieve a document from the storage by identifier.
     *
     * @param type $identifier The document identifier to look for.
     * @param type $revision The document revision to return.
     *
     * @return IDocument The document or null if there is no document for the given identifier.
     */
    public function fetchByIdentifier($identifier, $revision = NULL);
}

?>
