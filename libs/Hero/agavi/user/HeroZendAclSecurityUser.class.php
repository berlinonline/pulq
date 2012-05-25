<?php

/**
 * The HeroZendAclSecurityUser is responseable for detecting required scripts and deploying them for your view.
 *
 * @version         $Id: HeroZendAclSecurityUser.class.php 1010 2012-03-02 20:08:23Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Hero
 * @subpackage      Agavi/User
 */
class HeroZendAclSecurityUser extends AgaviSecurityUser implements Zend_Acl_Role_Interface
{
    protected $zendAcl;

    protected $accessConfig;

    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);

        $this->accessConfig = include AgaviConfigCache::checkConfig(
            AgaviConfig::get('core.config_dir') . '/access_control.xml'
        );
        $this->zendAcl = $this->createZendAcl();
    }

    protected function createZendAcl()
    {
        $zendAcl = new Zend_Acl();
        // setup our resources
        foreach ($this->accessConfig['resources'] as $resource => $def)
        {
            $zendAcl->addResource($resource, $def['parent']);
            // deny all actions to all users per default to require explicit grants.
            foreach ($def['actions'] as $action)
            {
                $zendAcl->deny(NULL, $resource, $action);
            }
        }

        // setup our roles
        foreach ($this->accessConfig['roles'] as $role => $def)
        {
            $zendAcl->addRole($role, $def['parent']);
            // apply all grants for the current role.
            foreach ($def['acl']['grant'] as $grant)
            {
                $operation = $grant['action'];
                $assertionTypeKey = $grant['constraint'];
                $resource = $this->accessConfig['resource_actions'][$operation];
                $zendAcl->allow($role, $resource, $operation, $this->createAssertion($assertionTypeKey));
            }
            // apply all denies for the current role.
            foreach ($def['acl']['deny'] as $deny)
            {
                $operation = $deny['action'];
                $assertionTypeKey = $deny['constraint'];
                $resource = $this->accessConfig['resource_actions'][$operation];
                $zendAcl->deny($role, $resource, $operation, $this->createAssertion($assertionTypeKey));
            }
        }
        return $zendAcl;
    }

    public function mapExternalRoleToDomain($origin, $role)
    {
        $roleKey = $origin . '::' . $role;
        if (! isset($this->accessConfig['external_roles'][$roleKey]))
        {
            return NULL;
        }
        return $this->accessConfig['external_roles'][$roleKey];
    }

    public function getZendAcl()
    {
        return $this->zendAcl;
    }

    public function isAllowed($resource, $operation = NULL)
    {

        return $this->getZendAcl()->isAllowed($this, $resource, $operation);
    }

    public function hasRole($role)
    {
        // could be our role directly, could be an ancestor, so check both
        return $this->getRoleId() == $role || $this->getZendAcl()->inheritsRole($this->getRoleId(), $role);
    }

    public function getRoleId()
    {
        if ($this->isAuthenticated() && $this->hasAttribute('acl_role'))
        {
            return $this->getAttribute('acl_role');
        }
        return $this->getParameter('default_acl_role', 'user');
    }

    /**
     *
     * @param mixed $credential
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function hasCredential($credential)
    {
        try
        {
            if ($credential instanceof Zend_Acl_Resource_Interface)
            {
                // an object instance was given; perform an access check on this (without an operation)
                return $this->isAllowed($credential);
            }

            if (!is_scalar($credential))
            {
                // can't do much with this...
                return FALSE;
            }

            $credential = explode('.', $credential, 2);
            if (count($credential) == 2)
            {
                // a string like "product.create"; check the ACL
                return $this->isAllowed($credential[0], $credential[1]);
            }
            else
            {
                // something like "administrator"; let's see if that's our role or an ancestor of it
                return $this->hasRole($credential[0]);
            }
        }
        catch (Zend_Acl_Exception $e)
        {
            return FALSE;
        }
    }

    protected function createAssertion($typeKey = NULL)
    {
        if (! $typeKey)
        {
            return NULL;
        }
        $assertionClass = implode('', array_map('ucfirst', explode('_', $typeKey))) . 'Assertion';
        if (! class_exists($assertionClass))
        {
            throw new InvalidArgumentException(
                "Invalid assertion type given in acl configuration. Can not resolve to class."
            );
        }
        return new $assertionClass;
    }
}

?>
