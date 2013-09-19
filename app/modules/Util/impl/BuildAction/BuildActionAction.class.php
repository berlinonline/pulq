<?php

use Pulq\CodeGen\Agavi\ActionBuilder;


class Util_BuildActionAction extends UtilBaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $module_name = $rd->getParameter('module');
        $action_name = $rd->getParameter('action');

        $builder = new ActionBuilder($module_name, $action_name);

        try {
            $builder->build();
        } catch (Exception $e) {
            $this->logError($e->getMessage());
            throw $e;
        }

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
