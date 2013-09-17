<?php

class Util_BuildConfig_BuildConfigSuccessView extends UtilBaseView 
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $msg = "";
        $this->getResponse()->setContent($msg);
    }
}
