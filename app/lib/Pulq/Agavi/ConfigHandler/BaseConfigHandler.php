<?php

namespace Pulq\Agavi\ConfigHandler;

abstract class BaseConfigHandler extends \AgaviXmlConfigHandler
{
    protected function parseSettings(\AgaviXmlConfigDomElement $settingsParent)
    {
        $settings = array();

        foreach ($settingsParent->getChildren('setting') as $settingElement)
        {
            $index = 
                $settingElement->hasAttribute('name') 
                ? trim($settingElement->getAttribute('name')) 
                : count(array_values($settings));

            if ($settingElement->hasChild('settings'))
            {
                $settings[$index] = $this->parseSettings(
                    $settingElement->getChild('settings')
                );
            }
            else if(1 < $settingElement->countChildren('setting'))
            {
                $settings[$index] = $this->parseSettings($settingElement);
            }
            else
            {
                $settings[$index] = \AgaviToolkit::expandDirectives(
                    trim($settingElement->getValue())
                );
            }
        }

        return $settings;
    }
}
