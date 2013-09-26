<?php

use Symfony\Component\Console\Output\ConsoleOutput;

class Util_RequireJs_RequireJsSuccessView extends UtilBaseView 
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $output = new ConsoleOutput();
        $output->writeln('<info>Sucessfully optimized JS bundle</info>');
    }
}
