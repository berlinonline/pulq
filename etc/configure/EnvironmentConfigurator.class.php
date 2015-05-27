<?php

use Pulq\Core\Environment;

/**
 * EnvironmentConfigurator provides an simple api to update/initialize your environment and host configuration.
 * It is meant for command line usage and required user interaction in 3 of 4 public methods (@see self::importHosts).
 *
 * @package    BerlinOnline
 * @subpackage Configure
 *
 * @author     Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @copyright  BerlinOnline GmbH & Co. KG
 */
class EnvironmentConfigurator
{
    // ---------------------------------- <CONSTANTS> ----------------------------------------------

    const CFG_DB_HOST = 'database.host';

    const CFG_DB_PORT = 'database.port';

    /**
     * Holds the char that we consider as a positve response from a user on the cli.
     */
    const CONFIRM_POSITIVE = 'y';

    /**
     * Holds the char that we consider as a positve response from a user on the cli.
     */
    const CONFIRM_NEGATIVE = 'n';

    // --------------------------------- </CONSTANTS> ----------------------------------------------


    // --------------------------------- <PUBLIC METHODS> --------------------------------------------

    /**
     * Starts an interactive user dialog prompting for basic environment information,
     * such as env-name, and database settings.
     *
     * @todo Add asset-basepath and further basic settings.
     */
    public function initializeMainConfig()
    {
        print("- Section: Common Settings" . PHP_EOL);
        $php_path = $this->promptPhpPath();
        $base_href = $this->promptBaseHref();
        while (!($environment = $this->promptEnvironment()));

        $config = array(
            Environment::CFG_PHP         => $php_path,
            Environment::CFG_ENVIRONMENT => $environment,
            Environment::CFG_BASE_HREF   => $base_href
        );

        $config_filepath = $this->getConfigFilePath();
        $config_settings = array();

        $config_dir = dirname($config_filepath);
        if (! is_dir($config_dir))
        {
            mkdir($config_dir, 0775, TRUE);
        }

        $this->generateConfig($config);
        $this->generateLocalConfigSh($config);
        $this->generateTestingConfig($config);
    }

    protected function promptPhpPath()
    {
        $php_command = NULL;
        $default_php_path = isset($_SERVER['PHP_COMMAND']) ? $_SERVER['PHP_COMMAND'] : @exec('which php');

        while (! $this->testPhp($php_command))
        {
            $php_command = $this->readline('Enter path to php', $default_php_path);
        }

        return $php_command;
    }

    protected function promptBaseHref()
    {
        $base_href = NULL;

        while (! trim($base_href))
        {
            $base_href = $this->readline('Enter the project\'s base url');
        }

        return trim($base_href);
    }

    protected function promptEnvironment()
    {
        $environment = NULL;

        while (!$this->testEnvironment($environment))
        {
            $environment = $this->readline('Enter environment');
        }

        $answer = $this->readline(
            "Environment is '" . $environment . PHP_EOL . "Are you sure you want to keep this?(y/n)"
        );

        return (self::CONFIRM_POSITIVE === $answer) ? $environment : FALSE;
    }

    protected function readline($label, $default = NULL, $promptchar = ':', $hide_input = FALSE)
    {
        print(
            empty($default)
            ? sprintf("%s%s ", $label, $promptchar)
            : sprintf("%s[%s]%s ", $label, $default, $promptchar)
        );

        if ($hide_input)
        {
            system('stty -echo');
        }

        $value = trim(fgets(STDIN));

        if ($hide_input)
        {
            system('stty echo');
            print(PHP_EOL);
        }

        if (0 === strlen($value) && NULL !== $default)
        {
            return $default;
        }

        return $value;
    }

    // --------------------------------- <USER PROMPTING> ---------------------------------------------


    // -------------------------------- <CONFIG GENERATION> -------------------------------------------

    protected function generateConfig(array $config)
    {
        $config_filepath = $this->getConfigFilePath();

        $config_code = sprintf(
            $this->getConfigCodeTemplateString(),
            var_export($config, TRUE)
        );

        if (FALSE === file_put_contents($config_filepath, $config_code))
        {
            die ('Can not write: '.$config_filepath);
        }
    }


    protected function generateTestingConfig(array $config)
    {
        $config_filepath = str_replace('/local.', '/local.testing.', $this->getConfigFilePath());
        $config[Environment::CFG_ENVIRONMENT] = 'testing.'.$config[Environment::CFG_ENVIRONMENT];

        $config_code = sprintf(
            $this->getConfigCodeTemplateString(),
            var_export($config, TRUE)
        );

        if (FALSE === file_put_contents($config_filepath, $config_code))
        {
            die ('Can not write: '.$config_filepath);
        }
    }


    protected function generateLocalConfigSh(array $config)
    {
        $sh_config_filepath = $this->getLocalConfigShFilePath();

        if (! file_exists($sh_config_filepath))
        {
            $config_code = sprintf(
                $this->getLocalShConfigCode(),
                $config[Environment::CFG_PHP],
                $config[Environment::CFG_BASE_HREF],
                $config[Environment::CFG_ENVIRONMENT]
            );

            if (FALSE === file_put_contents($sh_config_filepath, $config_code))
            {
                // @todo Throw an exception or warn about the error.
            }
        }
    }

    protected function getConfigCodeTemplateString()
    {
        return <<<PHP_CODE
<?php

/**
 * !CAUTION! Autogenerated code that was generated by the 'configure-env.php' script.
 * All modifications directly applied to this file,
 * may break or badly influence the operability of the apps relying on the contents of this file.
 *
 * !DO NOT EDIT!
 * Unless there is someone standing behind you with a loaded shotgun ready to create a mess and still you have to know what your doing or...
 * -> !DO NOT EDIT!
 */

return %s;
PHP_CODE;
    }

    protected function getLocalShConfigCode()
    {
        return <<<SH_CODE
#!/bin/bash
export PHP_COMMAND=%s
export BASE_HREF="%s"

if (test -z "\$AGAVI_ENVIRONMENT") ; then
   export AGAVI_ENVIRONMENT="%s"
fi

# Project base path
cw_path="`dirname $0`/.."
cw_path="`readlink -f \${cw_path}`"

# Nodejs libraries:
export PATH="\${cw_path}/node_modules/.bin:\$PATH"
SH_CODE;
    }

    // -------------------------------- </CONFIG GENERATION> -------------------------------------------


    // ---------------------------------- <VALUE CHECKING> ---------------------------------------------

    protected function testPhp($path)
    {
        if (empty($path)) return FALSE;

        $output = array();
        exec("$path -v", $output);

        if (1 < count($output) && preg_match('/PHP 5\.[456]/', $output[0]))
        {
            return TRUE;
        }

        return FALSE;
    }

    protected function testEnvironment($environment)
    {
        return ! empty($environment) && 3 <= strlen($environment);
    }

    // ---------------------------------- </VALUE CHECKING> ---------------------------------------------


    // ----------------------------------- <PATH HANDLING> ----------------------------------------------

    protected function getAppbasePath()
    {
        return dirname(dirname(dirname(__FILE__)));
    }

    protected function getLocalSettingsBasePath()
    {
        $base_dir = $this->getAppbasePath();

        return $base_dir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'local' . DIRECTORY_SEPARATOR;
    }

    protected function getConfigFilePath()
    {
        $local_dir = $this->getLocalSettingsBasePath();

        return $local_dir
            . Environment::CONFIG_FILE_PREFIX
            . Environment::CONFIG_FILE_NAME;
    }

    protected function getLocalConfigShFilePath()
    {
        $local_dir = $this->getLocalSettingsBasePath();

        return $local_dir . 'local.config.sh';
    }

    // ----------------------------------- </PATH HANDLING> ---------------------------------------------
}
