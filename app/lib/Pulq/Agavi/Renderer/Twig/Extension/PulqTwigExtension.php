<?php
namespace Pulq\Agavi\Renderer\Twig\Extension;

use \Netcarver\Textile\Parser as Textile;

class PulqTwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'Pulq';
    }

    public function getFilters()
    {
        $filters = array(
            new \Twig_SimpleFilter('textile', array($this, 'pulqTextileFilter'))
        );

        return $filters;
    }

    /**
     * textileThis()/textileRestricted()
     * 
     * With restricted Textile strip_tags is not needed, but minimum functionality is provided
     */
    public function pulqTextileFilter($value)
    {
        $value = strip_tags($value);
        $parser = new Textile('xhtml');

        return $parser->textileThis($value);
    }
}
