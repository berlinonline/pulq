<?php

namespace Pulq\CodeGen\Config;

class AccessControlXmlConfigGenerator extends DefaultXmlConfigGenerator
{
    public function generate($name, array $filesToInclude)
    {
        $document = $this->createDocument('resources');
        foreach ($filesToInclude['resources'] as $resourceConfig)
        {
            $include = $this->createResourceInclude($document, $resourceConfig);
            $resources = $document->getElementsByTagName('resources')->item(0);
            $resources->appendChild($include);
        }
        $this->writeConfigFile($document, 'resources');

        $document = $this->createDocument('permissions');
        foreach ($filesToInclude['permissions'] as $permissionsConfig)
        {
            $include = $this->createPermissionInclude($document, $permissionsConfig);
            $resources = $document->getElementsByTagName('acl')->item(0);
            $resources->appendChild($include);
        }
        $this->writeConfigFile($document, 'permissions');
    }

    protected function createResourceInclude(\DOMDocument $document, $resourceConfig)
    {
        $include = $document->createElement('xi:include');

        $include->setAttribute('href', str_replace(
            \AgaviConfig::get('core.app_dir'),
            '../..',
            $resourceConfig
        ));

        $include->setAttribute(
            'xpointer',
            "xmlns(ae=http://agavi.org/agavi/config/global/envelope/1.0) " .
            "xmlns(acl=http://berlinonline.de/schemas/pulq/config/access_control/1.0) " . 
            "xpointer(/ae:configurations/ae:configuration/acl:resources/*)"
        );

        return $include;
    }

    protected function createPermissionInclude(\DOMDocument $document, $permissionConfig)
    {
        $include = $document->createElement('xi:include');

        $include->setAttribute('href', str_replace(
            \AgaviConfig::get('core.app_dir'),
            '../..',
            $permissionConfig
        ));

        $include->setAttribute(
            'xpointer',
            "xmlns(ae=http://agavi.org/agavi/config/global/envelope/1.0) " .
            "xmlns(acl=http://berlinonline.de/schemas/pulq/config/access_control/1.0) " .
            "xpointer(/ae:configurations/ae:configuration/acl:roles/acl:role[@name='default-permissions']/acl:acl/*)"
        );

        return $include;
    }

    protected function createDocument($name)
    {
        $document = new \DOMDocument('1.0', 'utf-8');

        $root = $document->createElementNs(
            'http://agavi.org/agavi/config/global/envelope/1.0', 
            'ae:configurations'
        );

        $root->setAttribute('xmlns', 'http://berlinonline.de/schemas/pulq/config/access_control/1.0');
        $root->setAttribute('xmlns:ae','http://agavi.org/agavi/config/global/envelope/1.0');
        $root->setAttribute('xmlns:xi', 'http://www.w3.org/2001/XInclude');
        $root->setAttribute(
            'xmlns:env', 
            'http://berlinonline.de/schemas/pulq/config/envelope/definition/1.0'
        );

        $configuration = $document->createElement('ae:configuration');

        if ('permissions' === $name)
        {
            $roles = $document->createElement('roles');
            $role = $document->createElement('role');
            $role->setAttribute('name', 'default-permissions');
            $role->appendChild($document->createElement('acl'));
            $roles->appendChild($role);
            $configuration->appendChild($roles);
            $root->appendChild($configuration);
        }
        else
        {
            $configuration->appendChild($document->createElement('resources'));
            $root->appendChild($configuration);
        }
        
        $document->appendChild($root);

        return $document;
    }
}
