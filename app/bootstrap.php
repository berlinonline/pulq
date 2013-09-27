<?php

use Pulq\Core\Environment;

// +---------------------------------------------------------------------------+
// | Require and hence setup the agavi configuration.                          |
// +---------------------------------------------------------------------------+
$rootDir = dirname(dirname(__FILE__));
require $rootDir . str_replace('/', DIRECTORY_SEPARATOR, '/app/config.php');

// make generated files group writeable for easy switch between web/console
umask(02);

// load environment
Environment::load(FALSE);

// +---------------------------------------------------------------------------+
// | Initialize the framework. You may pass an environment name to this method.|
// | By default the 'development' environment sets Agavi into a debug mode.    |
// | In debug mode among other things the cache is cleaned on every request.   |
// +---------------------------------------------------------------------------+

// @todo Atm this is needed to support routes that rely on the $_SERVER var for their source attribute.
$_SERVER['AGAVI_ENVIRONMENT'] = Environment::toEnvString();
Agavi::bootstrap($_SERVER['AGAVI_ENVIRONMENT']);
AgaviConfig::set('core.default_context', $default_context);

//register module and project namespaces for autoloading
$cfg = AgaviConfig::get('core.config_dir') . '/namespaces.xml';
$namespaces = include(AgaviConfigCache::checkConfig($cfg));

#var_dump($namespaces);die();

$loader = new Composer\Autoload\Classloader();

foreach ($namespaces as $namespace => $dir) {
    $loader->add($namespace, $dir, true);
}

$loader->register();

#var_dump(new Pulq\Util\Foo());die();
