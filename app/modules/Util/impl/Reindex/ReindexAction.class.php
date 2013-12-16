<?php

use Pulq\Util\Agavi\Action\BaseAction;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

class Util_ReindexAction extends BaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $stdin = fopen('/dev/tty', 'r');
        $output = new ConsoleOutput();

        $dialog  =new DialogHelper($output);
        $dialog->setInputStream($stdin);

        $delete_old = $dialog->askConfirmation($output, "<error>Delete old index? (y/n)</error>", false);

        fclose($stdin);

        $db = $rd->getParameter('database');
        $db->reindex($delete_old);

        return "Success";
    }

    public function isSecure()
    {
        return FALSE;
    }
}

