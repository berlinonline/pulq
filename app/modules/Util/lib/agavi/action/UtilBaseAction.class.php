<?php

use \Pulq\Agavi\Action\BaseAction;

class UtilBaseAction extends BaseAction
{
    public function isSecure()
    {
        return false;
    }
}
