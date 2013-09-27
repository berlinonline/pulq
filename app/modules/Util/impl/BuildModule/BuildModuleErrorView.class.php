
<?php

use Pulq\Util\Agavi\View\BaseView;
use Symfony\Component\Console\Output\ConsoleOutput;

class Util_BuildModule_BuildModuleErrorView extends BaseView 
{
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $output = new ConsoleOutput();
        $module_name = $rd->getParameter('module');
        $output->writeln('<error>Could not create module "'.$module_name.'"</error>');

        $errors = $this->getContainer()->getValidationManager()->getErrors();
        foreach($errors as $argument => $argument_errors) {
            foreach ($argument_errors['messages'] as $error_message) {
                $output->writeln("<comment>[$argument]: $error_message</comment>");
            }
        }
    }
}

