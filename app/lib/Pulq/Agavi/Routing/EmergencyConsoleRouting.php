<?php

namespace Pulq\Agavi\Routing;

class EmergencyConsoleRouting extends \AgaviConsoleRouting
{

    protected function loadConfig()
    {
        $cfg = \AgaviConfig::get('core.config_dir') . '/emergency/routing.xml';
        // allow missing routing.xml when routing is not enabled
        if(!is_readable($cfg)) {
            throw new \Exception("Emergency routing file not found! ($cfg)");
        }
        $this->importRoutes(unserialize(file_get_contents(\AgaviConfigCache::checkConfig($cfg, $this->context->getName()))));
    }
}
