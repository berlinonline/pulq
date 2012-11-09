<?php
/**
 *
 *
 * @author tay
 * @version $Id:$
 * @since 08.11.2012
 *
 */
class Default_SetupDatabaseAction extends DefaultBaseAction
{


    /**
     * Handles the Console request method.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>A string containing the view name associated
     *                     with this action; or</li>
     *                     <li>An array with two indices: the parent module
     *                     of the view to be executed and the view to be
     *                     executed.</li>
     *                   </ul>^
     */
    public function executeConsole(AgaviRequestDataHolder $rd)
    {
        $db = $this->getContext()->getDatabaseManager()->getDatabase($rd->getParameter('db'));
        if ($db instanceof ElasticsearchCouchdbriverDatabase)
        {
            switch ($rd->getParameter('action'))
            {
                case 'create':
                    $db->createIndexAndRiver();
                    break;
                case 'switch':
                    $db->executeIndexSwitch();
                    break;
                case 'delete':
                    $db->deleteIndex();
                    break;
            }
        }
        elseif ($db instanceof IDatabaseSetup)
        {
            switch ($rd->getParameter('action'))
            {
                case 'create':
                    $db->setup(FALSE);
                    break;
                default:
                    error_log(__METHOD__.":".__LINE__." :: Unsupported action: " . $rd->getParameter('action'));
                break;
            }
        }
        return AgaviView::NONE;
    }

}

?>