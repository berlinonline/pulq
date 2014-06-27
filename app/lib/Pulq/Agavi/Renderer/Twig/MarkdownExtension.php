<?php

namespace Pulq\Agavi\Renderer\Twig;

use \Michelf\MarkdownExtra;
use Twig_Extension;

class MarkdownExtension extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            'markdown' => new \Twig_Filter_Method(
                $this,
                'parseMarkdown',
                array('is_safe' => array('html'))
            ),
            'markdown_safe' => new \Twig_Filter_Method(
                $this,
                'parseMarkdownSafe',
                array('is_safe' => array('html'))
            )
        );
    }

    public function parseMarkdown($content)
    {
        $engine = new MarkdownExtra();
        $engine->no_markup = false;

        return $engine->transform($content);
    }

    public function parseMarkdownSafe($content)
    {
        $engine = new MarkdownExtra();
        $engine->no_markup = true;

        return $engine->transform($content);
    }

    public function getName()
    {
        return 'markdown';
    }
}
