<?php

/**
 * The BaseDocument class is a simple base implementation of the IDocument interface
 * and implements the complete interface except the fromArray method which must be implemented
 * specifcally be each concrete subclass.
 *
 * @version $Id: BaseDocument.class.php 1001 2012-03-02 14:33:43Z tschmitt $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Project
 * @subpackage DataObject
 */
abstract class BaseDocument extends BaseDataObject implements IDocument
{
    /**
     * Holds the WorkflowItem's identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * Holds information on who created this item and when.
     *
     * @var array
     */
    protected $created;

    /**
     * Holds information on who was the last to modify this item and when.
     *
     * @var array
     */
    protected $lastModified;

    /**
     * Create a new BaseDocument instance from the given data.
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        parent::__construct($data);

        if (! $this->created)
        {
            $this->touch();
        }
    }

    /**
     * Returns the unique identifier of our aggregate root (IWorkflowItem).
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns the IContentItem's created date as an array,
     * containing data about by whom and when the item was created.
     * The provided date data is a ISO8601 UTC formatted string.
     * The provided user information is a string holding the username.
     *
     * @return array
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Returns the IContentItem's created date as an array,
     * containing data about by whom and when the item modified the last time.
     * The provided date data is a ISO8601 UTC formatted string.
     * The provided user information is a string holding the username.
     *
     * @return array
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Notify that a value has changed.
     *
     * @param string $propName
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    protected function onPropertyChanged($propName) // @codingStandardsIgnoreEnd
    {
        if (! $this->isHydrating())
        {
            $this->touch();
        }
    }

    /**
     * Update the document's modified timestamp.
     * If the created timestamp has not yet been set, it is initialized too.
     *
     * @param AgaviUser $user An optional user to use instead of resolving the current session user.
     *
     * @return BaseDocument This instance for fluent api support.
     */
    protected function touch(AgaviUser $user = NULL)
    {
        $user = $user ? $user : AgaviContext::getInstance()->getUser();
        $value = array(
            'date' => date(DATE_ISO8601),
            'user' => $user->getParameter('username', 'system')
        );
        if (! $this->created)
        {
            $this->created = $value;
        }
        $this->lastModified = $value;
        return $this;
    }
}

?>
