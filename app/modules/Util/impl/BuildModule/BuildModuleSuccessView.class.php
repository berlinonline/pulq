<?php

class Util_BuildModule_BuildModuleSuccessView extends UtilBaseView 
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $msg = "";
        $this->getResponse()->setContent($msg);
    }
}
