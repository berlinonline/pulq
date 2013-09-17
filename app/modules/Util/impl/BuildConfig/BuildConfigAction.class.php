<?php

use Pulq\CodeGen\Config\ModuleConfigBuilder;

class Util_BuildConfigAction extends UtilBaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $builder = new ModuleConfigBuilder();

        $builder->build();

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
