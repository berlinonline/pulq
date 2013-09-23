<?php

$default_context = 'console';
$rootDir = dirname(dirname(__FILE__));

if (in_array('--emergency', $argv)) {
    putenv('AGAVI_ENVIRONMENT=emergency');
}

require  $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

AgaviContext::getInstance('console')->getController()->dispatch();
