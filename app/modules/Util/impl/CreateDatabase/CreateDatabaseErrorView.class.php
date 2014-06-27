<?php

use Pulq\Util\Agavi\View\BaseView;
use Symfony\Component\Console\Output\ConsoleOutput;

class Util_CreateDatabase_CreateDatabaseErrorView extends BaseView 
{
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $output = new ConsoleOutput();
        $db_name = $rd->getParameter('database');
        $output->writeln('<error>Could not create database "'.$db_name.'"</error>');

        $errors = $this->getContainer()->getValidationManager()->getErrors();
        foreach($errors as $argument => $argument_errors) {
            foreach ($argument_errors['messages'] as $error_message) {
                $output->writeln("<comment>[$argument]: $error_message</comment>");
            }
        }
    }
}
