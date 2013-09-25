<?php

use Symfony\Component\Console\Output\ConsoleOutput;

class Util_BuildLinks_BuildLinksSuccessView extends UtilBaseView 
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $output = new ConsoleOutput();
        $output->writeln('<info>Sucessfully linked modules</info>');
    }
}
