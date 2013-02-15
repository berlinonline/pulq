<?php

use \Honeybee\CodeGen\Config\ConfigurationScanner;
use \Honeybee\CodeGen\Config\DefaultXmlConfigGenerator;
use \Honeybee\CodeGen\Config\RoutingXmlConfigGenerator;
use \Honeybee\CodeGen\Config\Dat0rAutoloadGenerator;
use \Honeybee\CodeGen\Config\AccessControlXmlConfigGenerator;

$rootDir = dirname(dirname(__FILE__));
$default_context = 'web';
require  $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';
// bootstrap the agavi build env, so we can use the autoloader code
// and have the AgaviModuleCheck class available.
require(
    str_replace(
        '/', DIRECTORY_SEPARATOR, 
        $rootDir.'/vendor/agavi/agavi/src/build/agavi/build.php'
    )
);
AgaviBuild::bootstrap();

$scanner = new ConfigurationScanner();
foreach ($scanner->scan() as $name => $files)
{
    $generator = NULL;

    switch ($name)
    {
        case 'routing':
            $generator = new RoutingXmlConfigGenerator();
            break;
            
        case 'dat0r';
            $generator = new Dat0rAutoloadGenerator();
            break;
        case 'access_control';
            $generator = new AccessControlXmlConfigGenerator();
            break;

        default:
            $generator = new DefaultXmlConfigGenerator();
            break;
    }

    $generator->generate($name, $files);
}

exit(0);
