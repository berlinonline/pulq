<?php

use Pulq\Util\Agavi\Action\BaseAction;
use Pulq\CodeGen\Project\ProjectBuilder;

class Util_BuildProjectAction extends BaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $builder = new ProjectBuilder();

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
