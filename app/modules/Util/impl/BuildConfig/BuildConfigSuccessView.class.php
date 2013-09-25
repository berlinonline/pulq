<?php

use Symfony\Component\Console\Output\ConsoleOutput;

class Util_BuildConfig_BuildConfigSuccessView extends UtilBaseView 
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $output = new ConsoleOutput();
        $output->writeln('<info>Sucessfully included module configs</info>');
    }
}
