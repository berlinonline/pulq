<?php

/**
 * The PulqResourceFilterConfig provides access to the resource filter settings.
 *
 * @version         $Id: PulqResourceFilterConfig.class.php 1010 2012-03-02 20:08:23Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Pulq
 * @subpackage      Agavi/Filter
 */
class PulqResourceFilterConfig
{
    const CFG_ENABLE_COMBINE = 'combine_scripts';

    const CFG_ENABLE_CACHING = 'use_caching';

    const CFG_ENABLE_COMPRESS = 'compress_scripts';

    const CFG_OUTPUT_TYPES = 'output_types';

    const CFG_CACHE_DIR = 'cache_dir';

    const CFG_SCRIPT_SETTINGS = 'script_settings';

    const CFG_PUB_DIR = 'pub_dir';

    protected static $supportedSettings = array(
        self::CFG_ENABLE_COMBINE,
        self::CFG_ENABLE_CACHING,
        self::CFG_ENABLE_COMPRESS,
        self::CFG_CACHE_DIR,
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

    public function getCacheDir()
    {
        return $this->get(self::CFG_CACHE_DIR);
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

    public function isCachingEnabled()
    {
        return $this->get(self::CFG_ENABLE_CACHING, FALSE);
    }

    protected function hydrateParameters(array $parameters)
    {
        $required = array(
            self::CFG_CACHE_DIR,
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


        $cacheDir = realpath($parameters[self::CFG_CACHE_DIR]);
        if (! is_writable($cacheDir))
        {
            throw new AgaviConfigurationException(
                "The given cache directory '$cacheDir' is not writeable!"
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

        $this->settings[self::CFG_CACHE_DIR] = $cacheDir;

        $this->settings[self::CFG_ENABLE_COMBINE] = (
            isset($parameters[self::CFG_ENABLE_COMBINE])
            &&
            TRUE === $parameters[self::CFG_ENABLE_COMBINE]
        );

        $this->settings[self::CFG_PUB_DIR] = realpath(
            dirname(AgaviConfig::get('core.app_dir')) . DIRECTORY_SEPARATOR . 'pub'
        );
    }
}
