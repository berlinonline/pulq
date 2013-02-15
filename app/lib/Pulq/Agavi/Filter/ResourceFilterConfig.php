<?php

namespace Pulq\Agavi\Filter;

/**
 * The ResourceFilterConfig provides access to the resource filter settings.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class ResourceFilterConfig
{
    const CFG_ENABLE_COMBINE = 'combine_scripts';

    const CFG_ENABLE_CACHING = 'use_caching';

    const CFG_ENABLE_COMPRESS = 'compress_scripts';

    const CFG_OUTPUT_TYPES = 'output_types';

    /**
     * Represents a setting that holds a path that we use as the base,
     * when building our cache and deploy directory paths.
     */
    const CFG_BASE_DIR = 'base_dir';

    const CFG_PUB_DIR = 'pub_dir';

    const CACHE_DIR = 'cache';

    const DEPLOY_DIR = 'deploy';

    protected static $supportedSettings = array(
        self::CFG_ENABLE_COMBINE,
        self::CFG_ENABLE_CACHING,
        self::CFG_ENABLE_COMPRESS,
        self::CFG_BASE_DIR,
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
            throw new \InvalidArgumentException(
                sprintf(
                    "The given setting: %s is not supported. Supported settings are:\n- %s",
                    $settingName,
                    implode("\n- ", self::$supportedSettings)
                )
            );
        }

        return isset($this->settings[$settingName]) ? $this->settings[$settingName] : $default;
    }

    public function getBaseDir()
    {
        return $this->get(self::CFG_BASE_DIR);
    }

    public function getCacheDir()
    {
        return $this->get(self::CFG_BASE_DIR) . DIRECTORY_SEPARATOR . self::CACHE_DIR;
    }

    public function getDeployDir()
    {
        return $this->get(self::CFG_BASE_DIR) . DIRECTORY_SEPARATOR . self::DEPLOY_DIR;
    }

    public function getPubDir()
    {
        return $this->get(self::CFG_PUB_DIR);
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
            self::CFG_BASE_DIR,
            self::CFG_OUTPUT_TYPES
        );

        foreach ($required as $req)
        {
            if (!isset($parameters[$req]))
            {
                throw new \AgaviConfigurationException(
                    "Missing required setting for '$req'!"
                );
            }
        }


        $baseDir = realpath($parameters[self::CFG_BASE_DIR]);
        if (! is_writable($baseDir))
        {
            throw new \AgaviConfigurationException(
                "The given base directory '$baseDir' is not writeable!"
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

        $this->settings[self::CFG_BASE_DIR] = $baseDir;

        $this->settings[self::CFG_ENABLE_COMBINE] = (
            isset($parameters[self::CFG_ENABLE_COMBINE])
            &&
            TRUE === $parameters[self::CFG_ENABLE_COMBINE]
        );

        $this->settings[self::CFG_ENABLE_CACHING] = (
            isset($parameters[self::CFG_ENABLE_CACHING])
            &&
            TRUE === $parameters[self::CFG_ENABLE_CACHING]
        );

        $this->settings[self::CFG_PUB_DIR] = realpath(
            dirname(\AgaviConfig::get('core.app_dir')) . DIRECTORY_SEPARATOR . 'pub'
        );
    }
}
