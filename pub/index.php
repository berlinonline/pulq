<?php

$default_context = // @todo do we really need/want this magic/stunt?
    preg_match('/\/binaries/i', $_SERVER['QUERY_STRING'])
    ? 'web_binaries'
    : 'web';

$rootDir = dirname(__DIR__);
require $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

if (FALSE !== strpos(\Pulq\Core\Environment::getEnvironment(), 'development'))
{
    PhpDebugToolbar::start(array(
        'js_location' => '/static/PhpDebugToolbar/PhpDebugToolbar.js',
        'css_location' => '/static/PhpDebugToolbar/PhpDebugToolbar.css'
    ));
}

AgaviContext::getInstance()->getController()->dispatch();
