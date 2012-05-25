<?php

$rootDir = dirname(dirname(__FILE__));
require $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'dispatch.php';

AgaviConfig::set(
    'core.default_context',
    preg_match('/\/binaries/i', $_SERVER['QUERY_STRING'])
        ? 'web_binaries'
        : 'web'
);

AgaviContext::getInstance()->getController()->dispatch();
