<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package User
 */
class User_ReadAction extends UserBaseAction
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
        try
        {
            $user = $db->fetchUserById($rd->getParameter('user_id'));
        }
        catch (UserService_Exception_Found $exception)
        {
            $user = UserService_Status::create(FALSE, 404, "User not found");
        }

        $this->setAttribute('user', $user);
        return 'Success';
    }
}