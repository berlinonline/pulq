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
        if ($db instanceof IDatabaseSetupAction)
        {
            switch ($rd->getParameter('action'))
            {
                case 'create':
                    $db->actionCreate();
                    break;
                case 'create-tear-down':
                    $db->actionCreate(TRUE);
                    break;
                case 'switch':
                    $db->actionEnable();
                    break;
                case 'delete':
                    $db->actionDelete();
                    break;
                default:
                    PulqToolkit::log(__METHOD__, "Unsupported action: " . $rd->getParameter('action'), 'error');
            }
        }
        else 
        {
            PulqToolkit::log(__METHOD__, "Could not find database config: " . $rd->getParameter('db'));
        }
        return AgaviView::NONE;
    }

}
