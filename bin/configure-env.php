<?php

$rootDir = dirname(__DIR__);
require_once $rootDir . '/etc/configure/EnvironmentConfigurator.class.php';
require_once $rootDir . '/etc/configure/ConfigureEnvScript.class.php';
require_once $rootDir . '/vendor/autoload.php';

echo "Check environment and extensions ...\n";
if (ini_get('safe_mode'))
{
    die('Please switch off "safe_mode"');
}
foreach (array('ldap', 'fileinfo') as $extension)
{
    if (! extension_loaded($extension))
    {
        die('Please install and enable PHP extension: '.$extension);
    }
}
echo "pass.\n\n";

$configure_script = new ConfigureEnvScript();
$configure_script->run($argv);
