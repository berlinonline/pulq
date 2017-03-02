<?php
namespace Pulq\Agavi\Renderer\Twig;

/**
 * Twig extension to have AgaviConfig methods available as simple
 * and short functions in twig templates. This should save some keystrokes.
 */
class AgaviConfigExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'ac' => new \Twig_Function_Method($this, 'ac'),
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
        return \AgaviConfig::get($setting_name, $default_value);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string extension name.
     */
    public function getName()
    {
        return 'AgaviConfig';
    }
}

