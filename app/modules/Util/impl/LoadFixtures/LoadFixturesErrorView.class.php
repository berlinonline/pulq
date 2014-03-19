<?php

use Pulq\Util\Agavi\View\BaseView;
use Symfony\Component\Console\Output\ConsoleOutput;

class Util_LoadFixtures_LoadFixturesErrorView extends BaseView 
{
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $output = new ConsoleOutput();
        $fixture_name = $rd->getParameter('fixture');
        $output->writeln('<error>Could not load fixture "'.$fixture_name.'"</error>');

        $errors = $this->getContainer()->getValidationManager()->getErrors();
        foreach($errors as $argument => $argument_errors) {
            foreach ($argument_errors['messages'] as $error_message) {
                $output->writeln("<comment>[$argument]: $error_message</comment>");
            }
        }
    }
}
