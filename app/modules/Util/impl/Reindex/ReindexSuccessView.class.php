<?php

use Pulq\Util\Agavi\View\BaseView;
use Symfony\Component\Console\Output\ConsoleOutput;

class Util_Reindex_ReindexSuccessView extends BaseView
{
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $output = new ConsoleOutput();
        $db = $rd->getParameter('database');
        $output->writeln('<info>Reindexed database "'.$db->getName().'"</info>');
    }
}

