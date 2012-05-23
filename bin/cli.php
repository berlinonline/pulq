<?php
$rootDir = dirname(dirname(__FILE__));
require  $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'dispatch.php';

AgaviConfig::set('core.default_context', 'console');
AgaviContext::getInstance('console')->getController()->dispatch();
?>