<?php

use Pulq\Util\Agavi\Action\BaseAction;
use Pulq\CodeGen\Config\ModuleConfigBuilder;

class Util_BuildConfigAction extends BaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $builder = new ModuleConfigBuilder();

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
