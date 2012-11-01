<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package User
 */
class User_DeleteAction extends UserBaseAction
{


    /**
     * Handles the Write request method.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>A string containing the view name associated
     *                     with this action; or</li>
     *                     <li>An array with two indices: the parent module
     *                     of the view to be executed and the view to be
     *                     executed.</li>
     *                   </ul>^
     */
    public function executeWrite(AgaviRequestDataHolder $rd)
    {
        $db = $this->getUserService_Database();
        $user_id = $rd->getParameter('user_id');
        try
        {
            $user = $db->fetchUserById($user_id);
        }
        catch (UserService_Exception_Found $exception)
        {
            $user = UserService_Status::create(FALSE, 404, "User not found");
        }
        $db->deleteUser($user);
        $user = UserService_Status::create(TRUE, 200, "User %s deleted", array($user_id));
        $this->setAttribute('user', $user);
        return 'Success';
    }
}