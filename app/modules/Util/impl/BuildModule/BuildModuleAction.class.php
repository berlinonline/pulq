<?php

use Pulq\CodeGen\Config\ModuleConfigBuilder;


class Util_BuildModuleAction extends UtilBaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        echo __METHOD__.PHP_EOL;
        var_dump($rd->getParameters());

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
