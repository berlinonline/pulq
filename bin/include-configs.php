<?php

use \Pulq\CodeGen\Config\ConfigurationScanner;
use \Pulq\CodeGen\Config\DefaultXmlConfigGenerator;
use \Pulq\CodeGen\Config\RoutingXmlConfigGenerator;
use \Pulq\CodeGen\Config\AccessControlXmlConfigGenerator;

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
