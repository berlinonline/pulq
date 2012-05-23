<?php

/**
 * ProjectAclConfigHandler parses configuration files that follow the midas access_control markup.
 *
 * @version    $Id: ProjectAclConfigHandler.class.php 1013 2012-03-02 21:28:23Z tschmitt $
 * @author     Thorsten Schmitt-Rink
 * @package    Project
 * @subpackage Agavi/ConfigHandler
 */
class ProjectAclConfigHandler extends AgaviXmlConfigHandler
{
    /**
     * Holds the name of the access_control document schema namespace.
     */
    const XML_NAMESPACE = 'http://berlinonline.de/schemas/midas/config/access_control/1.0';

    /**
     * An assoc array that maps external roles/groups/whatever to local domain roles.
     *
     * @var array
     */
    protected $externalRoles;

    /**
     * An assoc array that maps actions to resource types.
     *
     * @var array
     */
    protected $resourceActions;

    /**
     * Execute this configuration handler.
     *
     * @param      string An absolute filesystem path to a configuration file.
     * @param      string An optional context in which we are currently running.
     *
     * @return     string Data to be written to a cache file.
     *
     * @throws     <b>AgaviUnreadableException</b> If a requested configuration
     *                                             file does not exist or is not
     *                                             readable.
     * @throws     <b>AgaviParseException</b> If a requested configuration file is
     *                                        improperly formatted.
     */
    public function execute(AgaviXmlConfigDomDocument $document)
    {
        $this->resourceActions = array();
        $this->externalRoles = array();

        $document->setDefaultNamespace(self::XML_NAMESPACE, 'acl');
        $config = $document->documentURI;

        $parsedResources = array();
        $parsedRoles = array();
        /* @var $cfgNode AgaviXmlConfigDomElement */
        foreach ($document->getConfigurationElements() as $cfgNode)
        {
            $parsedResources = array_merge(
                $parsedResources,
                $this->parseRecources(
                    $cfgNode->getChild('resources')
                )
            );
            $parsedRoles = array_merge(
                $parsedRoles,
                $this->parseRoles(
                    $cfgNode->getChild('roles')
                )
            );
        }

        $data = array();
        $data['roles'] = $parsedRoles;
        $data['resources'] = $parsedResources;
        $data['resource_actions'] = $this->resourceActions;
        $data['external_roles'] = $this->externalRoles;
        $configCode = sprintf('return %s;', var_export($data, TRUE));
        return $this->generate($configCode, $config);
    }

    /**
     * Parse the given resources node and return the corresponding array representation.
     *
     * @param AgaviXmlConfigDomElement $resourcesElement
     *
     * @return array
     */
    protected function parseRecources(AgaviXmlConfigDomElement $resourcesElement)
    {
        $parsedResources = array();
        foreach ($resourcesElement->get('resource') as $resourceElement)
        {
            $resource = $resourceElement->getAttribute('name');
            // parse actions
            $actionsElement = $resourceElement->getChild('actions');
            $actions = array();
            if ($actionsElement)
            {
                foreach ($actionsElement->get('action') as $actionNode)
                {
                    $action = $actionNode->nodeValue;
                    $actions[] = $action;
                    // setup a reverse action => resource lookup map.
                    $this->addResourceAction($action, $resource);
                }
            }
            $parsedResources[$resource] = array(
                'description' => $resourceElement->getChild('description')->nodeValue,
                'actions' => $actions,
                'parent' => $resourceElement->getAttribute('parent', NULL)
            );
        }
        return $parsedResources;
    }

    /**
     * Register a the given action as available for the given resource type.
     *
     * @param string $action
     * @param string $resourceId
     */
    protected function addResourceAction($action, $resourceId)
    {
        $this->resourceActions[$action] = $resourceId;
    }

    /**
     * Parse the given roles node and return the corresponding array representation.
     *
     * @param AgaviXmlConfigDomElement $rolesElement
     *
     * @return array
     */
    protected function parseRoles(AgaviXmlConfigDomElement $rolesElement)
    {
        $parsedRoles = array();
        foreach ($rolesElement->get('role') as $roleElement)
        {
            $role = $roleElement->getAttribute('name');

            // parse the members ...
            $members = array();
            $membersElement = $roleElement->getChild('members');
            if ($membersElement)
            {
                $members = $this->parseRoleMembers($membersElement);
                // Register role mappings (will allow to map for example ldap::user to news.editor).
                foreach ($members as $member)
                {
                    $externalRole = sprintf(
                        '%s::%s',
                        $member['type'],
                        $member['name']
                    );
                    $this->addExternalRole($externalRole, $role);
                }
            }

            // ..., parse the acl ...
            $acl = array(
                'grant' => array(),
                'deny' => array()
            );
            $aclElement = $roleElement->getChild('acl');
            if ($aclElement)
            {
                $acl = $this->parseRoleAcl($aclElement);
            }

            // ... then bring them together to define the role.
            $parsedRoles[$role] = array(
                'description' => $roleElement->getChild('description')->nodeValue,
                'members' => $members,
                'acl' => $acl,
                'parent' => $roleElement->getAttribute('parent', NULL)
            );
        }
        return $parsedRoles;
    }

    /**
     * Register a mapping from the given external role to the given domain role.
     *
     * @param string $externalRole
     * @param string $domainRole
     */
    protected function addExternalRole($externalRole, $domainRole)
    {
        $this->externalRoles[$externalRole] = $domainRole;
    }

    /**
     * Parse the given members node and return the corresponding array representation.
     *
     * @param AgaviXmlConfigDomElement $membersElement
     *
     * @return array
     */
    protected function parseRoleMembers(AgaviXmlConfigDomElement $membersElement)
    {
        $members = array();
        foreach ($membersElement->get('member') as $memberNode)
        {
            $members[] = array(
                'type' => $memberNode->getAttribute('type'),
                'name' => $memberNode->nodeValue
            );
        }
        return $members;
    }

    /**
     * Parse the given acl node and return the corresponding array representation.
     *
     * @param AgaviXmlConfigDomElement $aclElement
     *
     * @return array
     */
    protected function parseRoleAcl(AgaviXmlConfigDomElement $aclElement)
    {
        $acl = array(
            'grant' => array(),
            'deny' => array()
        );
        foreach ($aclElement->get('grant') as $grantNode)
        {
            $acl['grant'][] = array(
                'action' => $grantNode->nodeValue,
                'constraint' => $grantNode->getAttribute('if', NULL)
            );
        }
        foreach ($aclElement->get('deny') as $denyNode)
        {
            $acl['deny'][] = array(
                'action' => $denyNode->nodeValue,
                'constraint' => $denyNode->getAttribute('if', NULL)
            );
        }
        return $acl;
    }
}

?>
