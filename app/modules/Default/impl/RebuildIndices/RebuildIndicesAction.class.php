<?php

use Pulq\Agavi\Database\ElasticSearch;

class Default_RebuildIndicesAction extends DefaultBaseAction
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

        $db->setOutputCallback(function($string) {
            echo $string;
            flush();
        });

        if ($db instanceof Elasticsearch\RiverSetupDatabase)
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
        elseif ($db instanceof ElasticSearch\Database)
        {
            switch ($rd->getParameter('action'))
            {
                case 'create':
                    $db->createIndex();
                    break;
                default:
                    error_log(__METHOD__.":".__LINE__." :: Unsupported action: " . $rd->getParameter('action'));
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

    public function isSecure()
    {
        return FALSE;
    }
}

