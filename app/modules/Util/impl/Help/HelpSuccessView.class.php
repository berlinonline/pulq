<?php

use Pulq\Util\Agavi\View\BaseView;
use Symfony\Component\Console\Output\ConsoleOutput;

class Util_Help_HelpSuccessView extends BaseView 
{
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $loader = new Twig_Loader_Filesystem(array(
            dirname(__FILE__)
        ));
        $twig = new Twig_Environment($loader, array(
            'cache' => AgaviConfig::get('core.cache_dir').'/twig',
        ));
        $twig->addExtension(new Pulq\Agavi\Renderer\Twig\AgaviConfigExtension());
        $template = $twig->loadTemplate('HelpSuccess.twig');
        $content = $template->render(array());

        $output = new ConsoleOutput();
        $output->writeln($content);
    }
}
