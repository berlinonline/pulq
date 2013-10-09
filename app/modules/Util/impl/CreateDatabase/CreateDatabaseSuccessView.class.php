<?php

use Pulq\Util\Agavi\View\BaseView;
use Symfony\Component\Console\Output\ConsoleOutput;

class Util_CreateDatabase_CreateDatabaseSuccessView extends BaseView 
{
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $output = new ConsoleOutput();
        $db = $rd->getParameter('database');
        $output->writeln('<info>Created database "'.$db->getName().'"</info>');
    }
}
