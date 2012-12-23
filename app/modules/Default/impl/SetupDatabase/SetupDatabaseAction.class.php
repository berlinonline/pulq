<?php
/**
 * Setup a database from command line
 * 
 * Supported database classes must implement interface IDatabaseSetup
 *
 * @author tay
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
     * @return     AgaviView::NONE
     */
    public function executeConsole(AgaviRequestDataHolder $rd)
    {
        $dbm = $this->getContext()->getDatabaseManager();
        if (! $dbm)
        {
            throw new AgaviDatabaseException('Please enable setting "core.use_database"!');
        }
        
        $db = $dbm->getDatabase($rd->getParameter('db'));
        if ($db instanceof ElasticsearchCouchdbriverDatabase)
        {
            switch ($rd->getParameter('action'))
            {
                case 'create':
                case 'create-tear-down':
                    $db->createIndexAndRiver();
                    break;
                case 'switch':
                    $db->executeIndexSwitch();
                    break;
                case 'delete':
                    $db->deleteIndex();
                    break;
                default:
                    PulqToolkit::log(__METHOD__, "Unsupported action: " . $rd->getParameter('action'), 'error');
            }
        }
        elseif ($db instanceof IDatabaseSetup)
        {
            switch ($rd->getParameter('action'))
            {
                case 'create':
                    $db->setup(FALSE);
                    break;
                case 'create-tear-down':
                    $db->setup(TRUE);
                    break;
                default:
                    PulqToolkit::log(__METHOD__, "Unsupported action: " . $rd->getParameter('action'), 'error');
                break;
            }
        }
        else 
        {
            PulqToolkit::log(__METHOD__, "Could not find database config: " . $rd->getParameter('db'));
        }
        return AgaviView::NONE;
    }

}
