<?php

use Pulq\Util\Agavi\Action\BaseAction;

class Util_CreateDatabaseAction extends BaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $db = $rd->getParameter('database');
        $db->setup();

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
