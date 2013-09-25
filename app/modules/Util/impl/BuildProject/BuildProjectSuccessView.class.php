<?php

use Symfony\Component\Console\Output\ConsoleOutput;

class Util_BuildProject_BuildProjectSuccessView extends UtilBaseView 
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $output = new ConsoleOutput();
        $output->writeln('<info>Sucessfully generated project skeleton</info>');
    }
}
