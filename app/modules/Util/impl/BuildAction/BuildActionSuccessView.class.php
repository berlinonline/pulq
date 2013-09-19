<?php

use Symfony\Component\Console\Output\ConsoleOutput;

class Util_BuildAction_BuildActionSuccessView extends UtilBaseView 
{
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $output = new ConsoleOutput();
        $module_name = $rd->getParameter('module');
        $action_name = $rd->getParameter('action');
        $output->writeln('<info>Created action "'.$module_name.'/'.$action_name.'"</info>');
    }
}
