<?php

/**
 * The HeroScriptFilterConfig provides access to the scripts.xml settings.
 *
 * @version         $Id: HeroScriptFilterConfig.class.php 1010 2012-03-02 20:08:23Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Hero
 * @subpackage      Agavi/Filter
 */
class HeroScriptFilterConfig
{
    const CFG_ENABLE_COMBINE = 'combine_scripts';

    const CFG_ENABLE_COMPRESS = 'compress_scripts';

    const CFG_OUTPUT_TYPES = 'output_types';

    const CFG_JS_CACHE_DIR = 'js_cache';

    const CFG_CSS_CACHE_DIR = 'css_cache';

    const CFG_SCRIPT_SETTINGS = 'script_settings';

    const CFG_PUB_DIR = 'pub_dir';

    protected static $supportedSettings = array(
        self::CFG_ENABLE_COMBINE,
        self::CFG_ENABLE_COMPRESS,
        self::CFG_JS_CACHE_DIR,
        self::CFG_CSS_CACHE_DIR,
        self::CFG_SCRIPT_SETTINGS,
        self::CFG_OUTPUT_TYPES,
        self::CFG_PUB_DIR
    );

    protected $settings = array();

    public function __construct(array $parameters = array())
    {
        $this->hydrateParameters($parameters);
    }

    public function get($settingName, $default = NULL)
    {
        if (! in_array($settingName, self::$supportedSettings))
        {
            throw new InvalidArgumentException(
                sprintf(
                    "The given setting: %s is not supported. Supported settings are:\n- %s",
                    $settingName,
                    implode("\n- ", self::$supportedSettings)
                )
            );
        }

        return isset($this->settings[$settingName]) ? $this->settings[$settingName] : $default;
    }

    public function getJsCacheDir()
    {
        return $this->get(self::CFG_JS_CACHE_DIR);
    }

    public function getCssCacheDir()
    {
        return $this->get(self::CFG_CSS_CACHE_DIR);
    }

    public function getSupportedOutputTypes()
    {
        return $this->get(self::CFG_OUTPUT_TYPES, array());
    }

    public function isOutputTypeSupported($outputType)
    {
        $supportedOutputTypes = $this->get(self::CFG_OUTPUT_TYPES, array());

        if (empty($supportedOutputTypes))
        {
            return TRUE;
        }

        return in_array($outputType, $supportedOutputTypes);
    }

    public function isPackingEnabled()
    {
        return $this->get(self::CFG_ENABLE_COMBINE, FALSE);
    }

    public function getPackageDefinitions()
    {
        $scriptsConfiguration = $this->get(self::CFG_SCRIPT_SETTINGS, array());
        return $scriptsConfiguration['packages'];
    }

    public function getPackageNames()
    {
        $scriptsConfiguration = $this->get(self::CFG_SCRIPT_SETTINGS, array());
        return array_keys($scriptsConfiguration['packages']);
    }

    public function packageExists($packageName)
    {
        $packages = $this->getPackageDefinitions();
        return isset($packages[$packageName]);
    }

    public function getPackageData($packageName)
    {
        if (!$this->packageExists($packageName))
        {
            throw new Exception(
                sprintf(
                    "Encountered undefined script package: '%s'",
                    $packageName
                )
            );
        }

        $packages = $this->getPackageDefinitions();
        return $packages[$packageName];
    }

    public function getDeployments()
    {
        $scriptsConfiguration = $this->get(self::CFG_SCRIPT_SETTINGS);
        return $scriptsConfiguration['deployments'];
    }

    protected function hydrateParameters(array $parameters)
    {
        $required = array(
            self::CFG_CSS_CACHE_DIR,
            self::CFG_JS_CACHE_DIR,
            self::CFG_OUTPUT_TYPES
        );

        foreach ($required as $req)
        {
            if (!isset($parameters[$req]))
            {
                throw new AgaviConfigurationException(
                    "Missing required setting for '$req'!"
                );
            }
        }

        $cssCacheDir = realpath($parameters[self::CFG_CSS_CACHE_DIR]);
        if (! is_writable($cssCacheDir))
        {
            throw new AgaviConfigurationException(
                "The given css cache directory '$cssCacheDir' is not writeable!"
            );
        }

        $jsCacheDir = realpath($parameters[self::CFG_JS_CACHE_DIR]);
        if (! is_writable($jsCacheDir))
        {
            throw new AgaviConfigurationException(
                "The given js cache directory '$jsCacheDir' is not writeable!"
            );
        }

        $outputTypes = $parameters[self::CFG_OUTPUT_TYPES];
        if (is_string($outputTypes))
        {
            $this->settings[self::CFG_OUTPUT_TYPES] = array($outputTypes);
        }
        else
        {
            $this->settings[self::CFG_OUTPUT_TYPES] = $outputTypes;
        }

        $this->settings[self::CFG_JS_CACHE_DIR] = $jsCacheDir;
        $this->settings[self::CFG_CSS_CACHE_DIR] = $cssCacheDir;

        $this->settings[self::CFG_ENABLE_COMBINE] = (
            isset($parameters[self::CFG_ENABLE_COMBINE])
            &&
            TRUE === $parameters[self::CFG_ENABLE_COMBINE]
        );

        $this->settings[self::CFG_PUB_DIR] = realpath(
            dirname(AgaviConfig::get('core.app_dir')) . DIRECTORY_SEPARATOR . 'pub'
        );

        $configFile = AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR . 'scripts.xml';
        $this->settings[self::CFG_SCRIPT_SETTINGS] = include AgaviConfigCache::checkConfig($configFile);
    }
}

?>
