<?php

use Symfony\Component\Console\Output\ConsoleOutput;

class Util_Scss_ScssSuccessView extends UtilBaseView 
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $output = new ConsoleOutput();
        $output->writeln('<info>Sucessfully compiled SCSS</info>');
    }
}
