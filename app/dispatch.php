<?php

// +---------------------------------------------------------------------------+
// | Initialize some common directory vars and set our include path.           |
// +---------------------------------------------------------------------------+
$rootDir = dirname(dirname(__FILE__));
$libsDir = $rootDir . '/libs';
$ezComponentsDir = $libsDir . '/ezc';
$phpUnitDir = $libsDir . '/PHPUnit';
$zend = $libsDir . '/Zend';

$includes = array($libsDir, $ezComponentsDir, $phpUnitDir, $zend);
set_include_path(implode(PATH_SEPARATOR, $includes).PATH_SEPARATOR.get_include_path());

// make generated files group writeable for easy switch between web/console
umask(02);

require $libsDir . '/agavi/agavi.php';
require $rootDir . '/app/config.php';

if (isset($testingEnabled))
{
    require $rootDir . '/testing/config.php';
    require $libsDir . '/agavi/testing.php';
}

// +---------------------------------------------------------------------------+
// | Setup ezcomponents autoloading.                                           |
// +---------------------------------------------------------------------------+
require $ezComponentsDir . '/Base/src/ezc_bootstrap.php';
spl_autoload_register(array('ezcBase', 'autoload'));
// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our environment config provider.           |
// +---------------------------------------------------------------------------+
require $rootDir . '/app/lib/config/ProjectEnvironmentConfig.class.php';
ProjectEnvironmentConfig::load(isset($testingEnabled) && $testingEnabled);


// +---------------------------------------------------------------------------+
// | Initialize the framework. You may pass an environment name to this method.|
// | By default the 'development' environment sets Agavi into a debug mode.    |
// | In debug mode among other things the cache is cleaned on every request.   |
// +---------------------------------------------------------------------------+

// @todo Atm this is needed to support routes that rely on the $_SERVER var for their source attribute.
$_SERVER['AGAVI_ENVIRONMENT'] = ProjectEnvironmentConfig::toEnvString();

Agavi::bootstrap(
    ProjectEnvironmentConfig::toEnvString()
);