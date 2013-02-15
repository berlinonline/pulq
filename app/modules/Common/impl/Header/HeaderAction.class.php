<?php

class Common_HeaderAction extends CommonBaseAction
{
    public function execute(AgaviRequestDataHolder $parameters)
    {
        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}

?>
