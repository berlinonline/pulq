<?php

namespace Pulq\Agavi\Validator;

class ProjectModuleNameValidator extends \AgaviValidator
{
    protected function validate()
    {
        $module_name = $this->getData($this->getArgument());
        $module_dir = \AgaviConfig::get('core.app_dir') .
            '/../../project/modules/' . $module_name;

        if (is_dir($module_dir)) {
            $this->export($module_name, $this->getArgument());
            return true;
        } else {
            $this->throwError();
            return false;
        }
    }
}
