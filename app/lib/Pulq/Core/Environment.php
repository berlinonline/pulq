<?php

namespace Pulq\Core;

class Environment
{
    /**
     * The name of 'php' environment setting, a path to a php executable binary.
     */
    const CFG_PHP = 'php';

    /**
     * The name of 'hostname' environment setting.
     */
    const CFG_HOSTNAME = 'hostname';

    /**
     * The name of 'environment' config setting.
     */
    const CFG_ENVIRONMENT = 'environment';

    /**
     * The name of our base-href setting.
     */
    const CFG_BASE_HREF = 'base_href';

    /**
     * The name (wihtout prefix) of the local config file that holds our env-settings.
     */
    const CONFIG_FILE_NAME = 'config.php';

    /**
     * A file prefix that is used to indicate local only files.
     * This can be used to let your scm ignore local.* files
     * and prevent accidently comitting sensitive data.
     */
    const CONFIG_FILE_PREFIX = 'local.';

    /**
     * Holds an instance of this class.
     *
     * @var         Environment
     */
    protected static $instance;

    /**
     * Holds the data from our local config file.
     *
     * @var         array
     */
    protected $config;

    /**
     * Boolean flag that indicates whether we are in testing mode or not.
     *
     * @var         boolean
     */
    protected $testingEnabled;

    /**
     * Create a new Environment instance.
     *
     * @param       boolean $testingEnabled If testing is enabled
     */
    protected function __construct($testingEnabled = FALSE)
    {
        # find the base dir of the project
        $baseDir = realpath(dirname(__FILE__)."/../../../../../../../");

        $localConfigDir =
            $baseDir . DIRECTORY_SEPARATOR .
            'etc' . DIRECTORY_SEPARATOR .
            'local' . DIRECTORY_SEPARATOR;

        $configFilepath = $localConfigDir . static::CONFIG_FILE_PREFIX . static::CONFIG_FILE_NAME;

        $local_config = include($configFilepath);
        $this->config = $local_config['pulq_environment'];

        // No override allowed for testing environments.
        if (($env = getenv('AGAVI_ENVIRONMENT')) && TRUE !== $testingEnabled)
        {
            $this->config[static::CFG_ENVIRONMENT] = $env;
        }
    }

    /**
     * Initialize our config instance, thereby loading our evironment settings.
     *
     * @param       boolean $testingEnabled
     *
     * @return      Environment
     */
    public static function load($testingEnabled = FALSE)
    {
        if (NULL === static::$instance)
        {
            static::$instance = new Environment($testingEnabled);
        }

        return static::$instance;
    }

    /**
     * Return the current environment's string representation.
     *
     * @return      string
     */
    public static function toEnvString()
    {
        return static::getEnvironment();
    }

    /**
     * Return the current environment.
     *
     * @return      string
     */
    public static function getEnvironment()
    {
        return static::$instance->config[static::CFG_ENVIRONMENT];
    }

    /**
     * Return the path to the cli php binary to use for the current environment.
     *
     * @return      string
     */
    public static function getPhpPath()
    {
        return static::$instance->config[static::CFG_PHP];
    }

    /**
     * Return our current env's base url.
     *
     * @return      string
     */
    public static function getBaseHref()
    {
        return static::$instance->config[static::CFG_BASE_HREF];
    }

    /**
     * Tells you if we are currently in testing mode.
     * Not interesting most cases as the testing environment switches transparently
     * for your project code.
     *
     * @return      boolean
     */
    public static function isTestingEnabled()
    {
        return static::$instance->testingEnabled;
    }
}

