<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id: LockAction.class.php 21 2012-03-08 13:51:57Z mfischer $
 * @package User
 */
class User_LockAction extends UserBaseAction
{
    /**
     * Handles the Json request method.
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
    public function executeRead(AgaviRequestDataHolder $rd)
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
        if (!$user->isLocked())
        {
            $user = UserService_Status::create(FALSE, 404, "User is not locked");
        }
        $this->setAttribute('user', $user);
        return 'Success';
    }
}