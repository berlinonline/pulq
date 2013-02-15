<?php

namespace Pulq\CodeGen\Config;

class DefaultXmlConfigGenerator implements IConfigGenerator
{
    public function generate($name, array $filesToInclude)
    {
        $document = $this->createDocument($name);
        $root = $document->documentElement;

        foreach ($filesToInclude as $configFile)
        {
            $include = $this->createInclude($document, $configFile);
            $root->appendChild($include);
        }

        $this->writeConfigFile($document, $name);
    }

    protected function createDocument($name)
    {
        $document = new \DOMDocument('1.0', 'utf-8');
        $root = $document->createElementNs(
            'http://agavi.org/agavi/config/global/envelope/1.0',
            'ae:configurations'
        );
        $root->setAttribute(
            'xmlns',
            sprintf('http://agavi.org/agavi/config/parts/%s/1.0', $name)
        );
        $root->setAttribute('xmlns:xi', 'http://www.w3.org/2001/XInclude');
        $document->appendChild($root);

        return $document;
    }

    protected function createInclude(\DOMDocument $document, $includePath)
    {
        $include = $document->createElement('xi:include');
        $include->setAttribute('href', str_replace(
            \AgaviConfig::get('core.app_dir'), 
            '../..', 
            $includePath
        ));
        $include->setAttribute(
            'xpointer',
            'xmlns(ae=http://agavi.org/agavi/config/global/envelope/1.0) xpointer(/ae:configurations/*)'
        );

        return $include;
    }

    protected function writeConfigFile(\DOMDocument $document, $name)
    {
        $configIncludeDir = \AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR . 
            'includes' . DIRECTORY_SEPARATOR;
        $document->formatOutput = TRUE;
        $document->save($configIncludeDir.$name.'.xml');
    }
}
