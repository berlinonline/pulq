<?php

namespace Pulq\Agavi\Renderer;

use \AgaviConfig;
use \AgaviFileTemplateLayer;
use \AgaviTemplateLayer;
use \AgaviToolkit;
use \AgaviTwigRenderer;
use \Twig_Environment;
use \Twig_Loader_Filesystem;
use \Twig_Loader_String;
use \Twig_Template;

use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;

/**
 * Extends the AgaviTwigRenderer to add twig extensions via parameters. If you
 * need more functionality you should extend the AgaviTwigRenderer by yourself
 * and use that in the output_types.xml file.
 *
 */
class TwigRenderer extends AgaviTwigRenderer
{

    /**
     * Return an initialized Twig instance.
     *
     * @return Twig_Environment
     */
    protected function getEngine()
    {
        $twig = parent::getEngine();

        $this->addMarkdownExtension($twig);

        foreach ($this->getParameter('extensions', array()) as $extension_class_name)
        {
            $ext = new $extension_class_name();

            // as the renderer is reusable it may have the extension already
            if (!$twig->hasExtension($ext->getName()))
            {
                $twig->addExtension($ext);
            }
        }


        return $twig;
    }

    protected function addMarkdownExtension(Twig_Environment $twig)
    {
        $md_engine = $engine = new MarkdownEngine\DflydevMarkdownEngine();
        $twig->addExtension(new MarkdownExtension($engine));
    }
}
