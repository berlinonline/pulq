<?php
namespace Pulq\Agavi\Renderer\Twig\Extension;

use \Netcarver\Textile\Parser as Textile;
use \AgaviConfig;
use \Twig_Extension;
use \Twig_Function_Method;
use \Twig_SimpleFilter;

class PulqTwigExtension extends Twig_Extension
{
    public function getName()
    {
        return 'Pulq';
    }

    public function getFilters()
    {
        $filters = array(
            new Twig_SimpleFilter('textile', array($this, 'pulqTextileFilter'))
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

    public function getFunctions()
    {
        return array(
            'ac' => new Twig_Function_Method($this, 'ac'),
        );
    }

    /**
     * Returns the value for the given AgaviConfig setting key.
     *
     * @param string $setting_name key of setting to return
     * @param mixed $default_value value to return of key is not found
     *
     * @return mixed string of setting value or null if key not exists or array in case of nested parameters
     */
    public function ac($setting_name, $default_value = null)
    {
        return AgaviConfig::get($setting_name, $default_value);
    }
}
