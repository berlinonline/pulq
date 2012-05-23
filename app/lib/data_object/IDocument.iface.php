<?php

/**
 * The IDocument interface extends the IDataObject interface to add document specific attributes
 * such as identifier or created date and they serve as a base to all data-objects that shall be persisted.
 * Compared to plain IDataObjects, IDocuments are meant to be persisted which is the reason
 * why they serve an identifier and information on creation/modification dates.
 *
 * @version $Id: IDocument.iface.php 1001 2012-03-02 14:33:43Z tschmitt $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Project
 * @subpackage DataObject
 */
interface IDocument extends IDataObject
{
    /**
     * Returns the document's unique identifier.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Returns the IDocument's created date as an array,
     * containing data about by whom and when the document was created.
     * The provided date data is a ISO8601 UTC formatted string.
     * The provided user information is a string holding the username.
     *
     * <pre>
     * Value structure example:
     * array(
     *     'date' => '05-23-1985T15:23:78.123+01:00',
     *     'user' => 'shrink0r'
     * )
     * </pre>
     *
     * @return array
     */
    public function getCreated();

    /**
     * Returns the IDocument's modified date as an array,
     * containing data about by whom and when the document was modified.
     * The provided date data is a ISO8601 UTC formatted string.
     * The provided user information is a string holding the username.
     *
     * <pre>
     * Value structure example:
     * array(
     *     'date' => '05-23-1985T15:23:78.123+01:00',
     *     'user' => 'shrink0r'
     * )
     * </pre>
     *
     * @return array
     */
    public function getLastModified();
}

?>
