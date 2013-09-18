<?php

namespace Pulq\CodeGen;

class TwigBuilder
{
    protected $twig;
    protected $template_dir;

    public function __construct()
    {
        $loader = new \Twig_Loader_Filesystem($this->getTemplateDirs());
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => \AgaviConfig::get('core.cache_dir').'/twig',
        ));
    }

    protected function renderTemplate($template_file, array $context = array())
    {
        $template = $this->twig->loadTemplate($template_file);
        $content = $template->render($context);

        return $content;
    }

    protected function getTemplateDirs()
    {
        return array();
    }
}
