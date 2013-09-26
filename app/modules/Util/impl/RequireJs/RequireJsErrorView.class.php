<?php

use Symfony\Component\Console\Output\ConsoleOutput;

class Util_RequireJs_RequireJsErrorView extends UtilBaseView 
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $output = new ConsoleOutput();
        $output->writeln('<info>JS optimization failed</info>');
    }
}
