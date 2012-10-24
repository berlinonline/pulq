<?php

/**
 * PulqScriptsConfigHandler parses configuration files that follow the midas scripts.xsd markup.
 *
 * @version    $Id: PulqScriptsConfigHandler.class.php 1013 2012-03-02 21:28:23Z tschmitt $
 * @author     Thorsten Schmitt-Rink
 * @package    Pulq
 * @subpackage Agavi/ConfigHandler
 */
class PulqScriptsConfigHandler extends AgaviXmlConfigHandler
{
    const XML_NAMESPACE = 'http://berlinonline.de/schemas/midas/config/scripts/1.0';

    const XMLNS_PREFIX = 'scripts';

    const PACKAGE_JS = 'javascripts';

    const PACKAGE_CSS = 'stylesheets';

    public function execute(AgaviXmlConfigDomDocument $document)
    {
        $document->setDefaultNamespace(self::XML_NAMESPACE, self::XMLNS_PREFIX);
        $script_packages = array();
        $deployments = array();

        /* @var $cfg AgaviXmlConfigDomElement */
        foreach ($document->getConfigurationElements() as $cfg)
        {
            if ($cfg->hasChild('packages'))
            {
                $script_packages = $this->parsePackages($cfg->getChild('packages'));
            }

            if ($cfg->hasChild('deployments'))
            {
                $deployments = $this->parseDeployments($cfg->getChild('deployments'));
            }
        }

        $data = array(
            'packages' => $script_packages,
            'deployments' => $deployments
        );

        return sprintf(
            $this->getCodeTemplate(),
            date('Y-m-d H:i:s.u'),
            var_export($data, TRUE)
        );
    }
    /* ------------------------------ PACKAGE TYPE RELATED PROCESSING ---------------------------- */

    protected function parsePackages(AgaviXmlConfigDomElement $package_container)
    {
        $packages = array();

        foreach ($package_container->getChildren('package') as $package)
        {
            $package_name = $package->getAttribute('name');

            if (isset($packages[$package_name]))
            {
                throw new AgaviConfigurationException(
                    sprintf(
                        "The package '%s' has allready been defined.", $package_name
                    )
                );
            }

            $packages[$package_name] = $this->parsePackage($package);
        }

        return $packages;
    }

    protected function parsePackage(AgaviXmlConfigDomElement $package)
    {
        $package_data = array();
        $dependencies = array();
        $script_types = array('javascripts', 'stylesheets');

        foreach ($script_types as $script_type)
        {
            $package_data[$script_type] =
                $package->hasChild($script_type)
                ? $this->parseScriptCollection($package->getChild($script_type))
                : array();
        }

        if ($package->hasAttribute('depends'))
        {
            $depends_val = $package->getAttribute('depends');

            foreach (explode(' ', $depends_val) as $dep)
            {
                $dependencies[] = $dep;
            }
        }

        $package_data['deps'] = $dependencies;

        return $package_data;
    }
    /* ------------------------------ DEPLOYMENT TYPE RELATED PROCESSING ---------------------------- */

    protected function parseDeployments(AgaviXmlConfigDomElement $deployment_container)
    {
        $deployments = array();

        foreach ($deployment_container->getChildren('deployment') as $deployment)
        {
            $viewpath = $deployment->getAttribute('viewpath');

            if (isset($deployments[$viewpath]))
            {
                throw new AgaviConfigurationException(
                    sprintf(
                        "A deployment for the view path '%s' has allready been defined.",
                        $viewpath
                    )
                );
            }

            $deployments[$viewpath] = $this->parseDeployment($deployment);
        }

        return $deployments;
    }

    protected function parseDeployment(AgaviXmlConfigDomElement $deployment)
    {
        $deployment_data = array();
        $packages = array();
        $script_types = array('javascripts', 'stylesheets');

        foreach ($script_types as $script_type)
        {
            $deployment_data[$script_type] =
                $deployment->hasChild($script_type)
                ? $this->parseScriptCollection($deployment->getChild($script_type))
                : array();
        }

        if ($deployment->hasChild('package'))
        {
            foreach ($deployment->getChildren('package') as $package)
            {
                $packages[] = $package->getAttribute('ref');
            }
        }

        $deployment_data['packages'] = $packages;

        return $deployment_data;
    }
    /* ------------------------------ SCRIPT TYPE RELATED PROCESSING ---------------------------- */

    protected function parseScriptCollection(AgaviXmlConfigDomElement $script_collection)
    {
        $scripts = array();
        $pub_dir = dirname(AgaviConfig::get('core.app_dir')) . DIRECTORY_SEPARATOR . 'pub' . DIRECTORY_SEPARATOR;

        foreach ($script_collection->getChildren('script') as $script)
        {
            $src_type = $script->getAttribute('type', 'local');
            $script_path = $script->getValue();
            $full_path = $pub_dir . $script_path;

            if ('local' === $src_type && !file_exists($full_path))
            {
                throw new AgaviConfigurationException(
                    sprintf(
                        "Failed to find a specified scriptfile on the filesystem. Please check your configuration.
                        Affected path: %s",
                        $full_path
                    )
                );
            }

            $scripts[] = $script_path;
        }

        return $scripts;
    }

    protected function getCodeTemplate()
    {
        return <<<TPL
<?php
/**
 * Autogenerated cache file containing the settings for all scripts,
 * that shall be deployed to the client.
 * Generated on: %s
 */

return %s;

?>
TPL;
    }
}

?>