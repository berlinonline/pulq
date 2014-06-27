<?php

use Pulq\Util\Agavi\View\BaseView;
use Symfony\Component\Console\Output\ConsoleOutput;

class Util_BuildProject_BuildProjectSuccessView extends BaseView 
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $output = new ConsoleOutput();
        $output->writeln('<info>Sucessfully generated project skeleton</info>');
    }
}
