<?php

use Pulq\Util\Agavi\View\BaseView;
use Symfony\Component\Console\Output\ConsoleOutput;

class Util_LoadFixtures_LoadFixturesSuccessView extends BaseView 
{
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $output = new ConsoleOutput();
        $fixture_name = $rd->getParameter('fixture');
        $output->writeln('<info>Created fixture set "'.$fixture_name.'"</info>');
    }
}
