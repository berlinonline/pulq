<?php

use Symfony\Component\Console\Output\ConsoleOutput;

class Util_LinkModules_LinkModulesSuccessView extends UtilBaseView 
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $output = new ConsoleOutput();
        $output->writeln('<info>Sucessfully linked modules</info>');
    }
}
