<?php

use Pulq\Util\Agavi\View\BaseView;
use Symfony\Component\Console\Output\ConsoleOutput;

class Util_Scss_ScssErrorView extends BaseView
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $output = new ConsoleOutput();
        $output->writeln('<error>SCSS compilation failed</error>');
    }
}
