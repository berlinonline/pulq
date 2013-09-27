<?php

use Pulq\Util\Agavi\View\BaseView;
use Symfony\Component\Console\Output\ConsoleOutput;

class Util_BuildModule_BuildModuleSuccessView extends BaseView 
{
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $output = new ConsoleOutput();
        $module_name = $rd->getParameter('module');
        $output->writeln('<info>Created module "'.$module_name.'"</info>');

        # forward to BuildAction because an empty module does not make sense.
        return $this->createForwardContainer('Util', 'BuildAction', array(
            'module' => $module_name
        ));
    }
}
