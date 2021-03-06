<?php

use Pulq\Consumer\Agavi\View\BaseView;
use Symfony\Component\Console\Output\ConsoleOutput;

class Consumer_Push_PushErrorView extends BaseView 
{
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $output = array(
            'status' => array(
                'success' => false,
                'errors' => array()
            )
        );

        $errors = $this->getContainer()->getValidationManager()->getErrors();
        foreach($errors as $argument => $argument_errors) {
            foreach ($argument_errors['messages'] as $error_message) {
                $output['status']['errors'][$argument] = $error_message;
            }
        }

        return json_encode($output);
    }

    public function throwOutputTypeNotImplementedException()
    {
        die('Output type "'.$this->getContainer()->getOutputType()->getName().'" not implemented');
    }
}
