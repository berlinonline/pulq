<?php

namespace Pulq\CodeGen\Config;

class RoutingXmlConfigGenerator extends DefaultXmlConfigGenerator
{
    public function generate($name, array $filesToInclude)
    {
        $document = $this->createDocument($name);
        $root = $document->documentElement;
        
        $webConfig = $document->createElement('ae:configuration');
        $webConfig->setAttribute('context', 'web');
        $root->appendChild($webConfig);

        $consoleConfig = $document->createElement('ae:configuration');
        $consoleConfig->setAttribute('context', 'console');
        $root->appendChild($consoleConfig);

        $document->appendChild($root);
        $routesNode = $document->createElement('routes');
        $webConfig->appendChild($routesNode);

        foreach ($filesToInclude as $configFile)
        {
            $routesNode->appendChild(
                $this->createWebRouting($document, $configFile)
            );
            $consoleConfig->appendChild(
                $this->createConsoleRouting($document, $configFile)
            );
        }

        $this->writeConfigFile($document, $name);
    }

    protected function createConsoleRouting(\DOMDocument $document, $configFile)
    {
        $moduleRoutes = $document->createElement('xi:include');
        $moduleRoutes->setAttribute('href', str_replace(
            \AgaviConfig::get('core.app_dir'),
            '../..',
            $configFile
        ));
        $moduleRoutes->setAttribute(
            'xpointer',
            "xmlns(ae=http://agavi.org/agavi/config/global/envelope/1.0) xpointer(/ae:configurations/ae:configuration[@context='console'])/"
        );

        return $moduleRoutes;
    }

    protected function createWebRouting(\DOMDocument $document, $configFile)
    {
        $moduleName = $this->extractModuleNameFromPath($configFile);
        $moduleDefinition = str_replace('/', DIRECTORY_SEPARATOR,
             \AgaviConfig::get('core.modules_dir').'/'.$moduleName.'/config/dat0r/module.xml'
        );

        $moduleRoute = $document->createElement('route');
        $moduleRoute->setAttribute('name', strtolower($moduleName));
        $moduleRoute->setAttribute('pattern', '^/' . strtolower($moduleName));
        $moduleRoute->setAttribute('module', $moduleName);
        
        if (file_exists($moduleDefinition))
        {
            $callbacks = $document->createElement('callbacks');
            $callback = $document->createElement('callback');
            $callback->setAttribute('class', 'Pulq\\Agavi\\Routing\\ModuleRoutingCallback');
            $callbacks->appendChild($callback);

            $parameter = $document->createElement(
                'ae:parameter', 
                sprintf('Pulq\\Domain\\%1$s\\%1$sModule', $moduleName)
            );
            $parameter->setAttribute('name', 'implementor');

            $moduleRoute->appendChild($parameter);
            $moduleRoute->appendChild($callbacks);
        }

        $webInclude = $document->createElement('xi:include');
        $webInclude->setAttribute('href', str_replace(
            \AgaviConfig::get('core.app_dir'), 
            '../..', 
            $configFile
        ));
        $webInclude->setAttribute(
            'xpointer',
            "xmlns(ae=http://agavi.org/agavi/config/global/envelope/1.0) xmlns(r=http://agavi.org/agavi/config/parts/routing/1.0) xpointer(//ae:configuration[@context='web']/r:routes/r:route)/"
        );

        $moduleRoute->appendChild($webInclude);
        
        return $moduleRoute;
    }

    protected function extractModuleNameFromPath($path)
    {
        return str_replace(
            '/config/routing.xml', 
            '', 
            str_replace(
                \AgaviConfig::get('core.app_dir').'/modules/', '', $path
            )
        );
    }
}
