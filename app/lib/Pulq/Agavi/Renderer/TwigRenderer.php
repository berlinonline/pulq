<?php

namespace Pulq\Agavi\Renderer;

use Pulq\Agavi\Renderer\Twig\MarkdownExtension;

/**
 * Extends the AgaviTwigRenderer to add twig extensions via parameters. If you
 * need more functionality you should extend the AgaviTwigRenderer by yourself
 * and use that in the output_types.xml file.
 *
 */
class TwigRenderer extends \AgaviTwigRenderer
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

        foreach ($this->getParameter('extensions', array()) as $extension_class_name) {
            $ext = new $extension_class_name();

            // as the renderer is reusable it may have the extension already
            if (!$twig->hasExtension($ext->getName())) {
                $twig->addExtension($ext);
            }
        }


        return $twig;
    }

    protected function addMarkdownExtension(\Twig_Environment $twig)
    {
        $md_extension = new MarkdownExtension($twig);
        if (!$twig->hasExtension($md_extension->getName())) {
            $twig->addExtension($md_extension);
        }
    }
}
