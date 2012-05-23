<?php
/**
 * The UserIsItemOwnerAssertion is responseable for asserting that a the current user owns
 * a given workflow item resource.
 *
 * @version         $Id: UserIsItemOwnerAssertion.class.php 1010 2012-03-02 20:08:23Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      Agavi/User
 */
class UserIsItemOwnerAssertion implements Zend_Acl_Assert_Interface
{
    /**
     * Assert that a the given ProjectZendAcelUser ($role) owns the given WorkflowItem ($resource).
     *
     * @param Zend_Acl $acl
     * @param Zend_Acl_Role_Interface $role
     * @param Zend_Acl_Resource_Interface $resource
     * @param string $privilege
     *
     * @return boolean Returns true if the given role owns the provided resource.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = NULL, Zend_Acl_Resource_Interface $resource = NULL, $privilege = NULL) // @codingStandardsIgnoreEnd
    {
        if (!($resource instanceof IWorkflowItem))
        {
            // in case the check is performed without a specific workflow-item instance:
            // let's assume that the user can edit a generic workflow-item.
            return FALSE;
        }

        if (!($role instanceof ProjectZendAclSecurityUser))
        {
            // in case the check is performed without a specific user instance:
            // let's assume that any generic user cannot edit this workflow-item.
            return FALSE;
        }
        return $resource->getOwnerName() == $role->getAttribute('login');
    }

}

?>
