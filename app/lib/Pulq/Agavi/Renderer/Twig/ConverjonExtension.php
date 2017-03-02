<?php
namespace Pulq\Agavi\Renderer\Twig;

use Pulq\Services\AssetService;

/**
 * Twig extension to have convenience methods for handling Converjon in templates.
 */
class ConverjonExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'cv' => new \Twig_Function_Method($this, 'cv'),
        );
    }

    /**
     * Returns the URL that gets the image though Converjon
     *
     * @param string $url The original URL of the image
     * @param mixed $params Parameters to control Converjon
     *
     * @return mixed string of setting value or null if key not exists or array in case of nested parameters
     */
    public function cv($url, $params)
    {
        return AssetService::getConverjonUrl($url, $params);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string extension name.
     */
    public function getName()
    {
        return 'Converjon';
    }
}

