<?php

use Pulq\Util\Agavi\Action\BaseAction;

class Util_ReindexAction extends BaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $db = $rd->getParameter('database');
        $db->reindex();

        return "Success";
    }

    public function isSecure()
    {
        return FALSE;
    }
}

