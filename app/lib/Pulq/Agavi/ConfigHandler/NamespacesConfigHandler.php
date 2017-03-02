<?php

namespace Pulq\Agavi\ConfigHandler;

class NamespacesConfigHandler extends \AgaviXmlConfigHandler
{
    const XML_NAMESPACE = 'http://berlinonline.de/schemas/pulq/config/namespaces/1.0';

    public function execute(\AgaviXmlConfigDomDocument $document)
    {
        $this->resourceActions = array();
        $this->externalRoles = array();

        $document->setDefaultNamespace(self::XML_NAMESPACE, 'namespace');
        $config = $document->documentURI;

        $data = array();

        foreach ($document->getConfigurationElements() as $cfgNode)
        {
            $namespaces = $cfgNode->getChild('namespaces');
            foreach($namespaces->get('namespace') as $namespace) {
                $name = $namespace->getAttribute('name');
                $value = \AgaviToolkit::expandDirectives($namespace->getValue());
                $data[$name] = $value;
            }
        }

        $configCode = sprintf('return %s;', var_export($data, TRUE));
        return $this->generate($configCode, $config);
    }
}
