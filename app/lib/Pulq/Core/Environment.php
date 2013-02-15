<?php

namespace Pulq\Core;

/**
 * The Environment provides essential settings for bootstrapping this application,
 * such as the environment to use and depending on that load the correct application settings.
 * The settings provided by this class are always in a local (environment) dedicated and therefore
 * the bin/configure-env script must be run once to set things up before a fresh application can be run.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class Environment
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * The name of 'database' environment setting.
     */
    const CFG_DB = 'database';

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
     * The name of our database-host setting.
     */
    const CFG_DB_HOST = 'host';

    /**
     * The name of our database-port setting.
     */
    const CFG_DB_PORT = 'port';

    /**
     * The name of our base-href setting.
     */
    const CFG_BASE_HREF = 'base_href';

    /**
     * The name (wihtout prefix) of the local config file that holds our env-settings.
     */
    const CONFIG_FILE_NAME = 'environment.php';

    /**
     * A file prefix that is used to indicate local only files.
     * This can be used to let your scm ignore local.* files
     * and prevent accidently comitting sensitive data.
     */
    const CONFIG_FILE_PREFIX = 'local.';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds an instance of this class.
     *
     * @var         Environment
     */
    private static $instance;

    /**
     * Holds the data from our local config file.
     *
     * @var         array
     */
    private $config;

    /**
     * The hostname of the host we're running on.
     * May be faked for cli requests in order to still generate web urls.
     *
     * @var         string
     */
    private $currentHost;

    /**
     * Boolean flag that indicates whether we are in testing mode or not.
     *
     * @var         boolean
     */
    private $testingEnabled;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------

    /**
     * Create a new Environment instance.
     *
     * @param       boolean $testingEnabled If testing is enabled
     */
    private function __construct($testingEnabled = FALSE)
    {
        $this->testingEnabled = $testingEnabled;

        $baseDir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

        $localConfigDir =
            $baseDir . DIRECTORY_SEPARATOR .
            'etc' . DIRECTORY_SEPARATOR .
            'local' . DIRECTORY_SEPARATOR;

        $filename = $this->testingEnabled ? 'testing.' . self::CONFIG_FILE_NAME : self::CONFIG_FILE_NAME;
        $configFilepath = $localConfigDir . self::CONFIG_FILE_PREFIX . $filename;

        $this->config = include($configFilepath);

        if (isset($_SERVER['HTTP_HOST']))
        {
            $this->currentHost = $_SERVER['HTTP_HOST'];
        }
        // No override allowed for testing environments.
        if (($env = getenv('AGAVI_ENVIRONMENT')) && TRUE !== $testingEnabled)
        {
            $this->config[self::CFG_ENVIRONMENT] = $env;
        }
    }

    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Initialize our config instance, thereby loading our evironment settings.
     *
     * @param       boolean $testingEnabled
     *
     * @return      Environment
     */
    public static function load($testingEnabled = FALSE)
    {
        if (NULL === self::$instance)
        {
            self::$instance = new Environment($testingEnabled);
        }

        return self::$instance;
    }

    /**
     * Return the current environment's string representation.
     *
     * @return      string
     */
    public static function toEnvString()
    {
        return self::getEnvironment();
    }

    /**
     * Return the current environment.
     *
     * @return      string
     */
    public static function getEnvironment()
    {
        return self::$instance->config[self::CFG_ENVIRONMENT];
    }

    /**
     * Return the path to the cli php binary to use for the current environment.
     *
     * @return      string
     */
    public static function getPhpPath()
    {
        return self::$instance->config[self::CFG_PHP];
    }

    /**
     * Return an array containing our current env specific database settings.
     *
     * @return      array
     */
    public static function getDatabaseSettings()
    {
        return self::$instance->config[self::CFG_DB];
    }

    /**
     * Return our current env's database port setting.
     *
     * @return      int
     */
    public static function getDatabasePort()
    {
        $db_settings = self::getDatabaseSettings();

        return $db_settings[self::CFG_DB_PORT];
    }

    /**
     * Return our current env's database host setting.
     *
     * @return      string
     */
    public static function getDatabaseHost()
    {
        $db_settings = self::getDatabaseSettings();

        return $db_settings[self::CFG_DB_HOST];
    }

    /**
     * Return our current env's base url.
     *
     * @return      string
     */
    public static function getBaseHref()
    {
        return self::$instance->config[self::CFG_BASE_HREF];
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
        return self::$instance->testingEnabled;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
}

?>