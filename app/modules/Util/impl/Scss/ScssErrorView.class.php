<?php

use Symfony\Component\Console\Output\ConsoleOutput;

class Util_Scss_ScssErrorView extends UtilBaseView 
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $output = new ConsoleOutput();
        $output->writeln('<error>SCSS compilation failed</error>');
    }
}
