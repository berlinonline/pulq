<?php

use Honeybee\Agavi\Filter;

$default_context = 'web';
$rootDir = dirname(dirname(__FILE__));
require  $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

require(
    str_replace(
        '/', DIRECTORY_SEPARATOR, 
        $rootDir.'/vendor/agavi/agavi/src/build/agavi/build.php'
    )
);
AgaviBuild::bootstrap();

$modules = array();
foreach(new DirectoryIterator(AgaviConfig::get('core.modules_dir')) as $file) 
{
    if($file->isDot()) 
    {
        continue;
    }
    
    $check = new AgaviModuleFilesystemCheck();
    $check->setConfigDirectory('config');
    $check->setPath($file->getPathname());

    if($check->check()) 
    {
        $modules[] = (string)$file;
    }
}

echo PHP_EOL . "1. Will deploy resources for the following modules: " . PHP_EOL;
echo implode(', ', $modules) . PHP_EOL;
echo PHP_EOL . "2. Starting resource deployment ..." . PHP_EOL;

$packer = new Filter\ResourcePacker(
    array('html' => $modules), 
    'html', 
    new Filter\ResourceFilterConfig(array(
        Filter\ResourceFilterConfig::CFG_OUTPUT_TYPES => array('html'),
        Filter\ResourceFilterConfig::CFG_BASE_DIR => str_replace(
            '/', DIRECTORY_SEPARATOR, $rootDir.'/pub/static'
        ),
        Filter\ResourceFilterConfig::CFG_ENABLE_COMBINE => TRUE,
        Filter\ResourceFilterConfig::CFG_ENABLE_COMPRESS => TRUE
    ))
);

$packer->pack();

echo PHP_EOL . "Finished combining, compressing and copying resources." . PHP_EOL;

exit(0);
