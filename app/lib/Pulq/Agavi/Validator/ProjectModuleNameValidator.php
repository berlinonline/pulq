<?php

namespace Pulq\Agavi\Validator;
use \AgaviValidator;
use \AgaviConfig;

class ProjectModuleNameValidator extends AgaviValidator
{
    protected function validate()
    {
        $module_name = $this->getData($this->getArgument());
        $module_dir = AgaviConfig::get('core.modules_dir') . '/' . $module_name;

        if (is_dir($module_dir)) {
            $this->export($module_name, $this->getArgument());
            return true;
        } else {
            $this->throwError();
            return false;
        }
    }
}
