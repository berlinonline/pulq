<?php

use Pulq\Util\Agavi\Action\BaseAction;
use Pulq\CodeGen\Agavi\ModuleBuilder;


class Util_BuildModuleAction extends BaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $module_name = $rd->getParameter('module');

        $builder = new ModuleBuilder($module_name);

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
