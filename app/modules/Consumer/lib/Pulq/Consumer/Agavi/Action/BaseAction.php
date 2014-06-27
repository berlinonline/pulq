<?php

namespace Pulq\Consumer\Agavi\Action;

use Pulq\Agavi\Action\BaseAction as PulqBaseAction;

class BaseAction extends PulqBaseAction
{
    public function isSecure()
    {
        return false;
    }
}
