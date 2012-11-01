<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package User
 */
class User_UpdateAction extends UserBaseAction
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
        $user_id = $rd->getParameter('user_id');
        $user_content = $rd->getFile('post_file')->getContents();
        $db = $this->getUserService_Database();
        if (!$db->existsId($user_id))
        {
            $status = UserService_Status::create(FALSE, 404, "User with ID '%s' does not exists", array($user_id));
            $this->setAttribute('user', $status);
            return 'Error';
        }
        $user = $db->fetchUserById($user_id);//Do not overwrite missing values;
        $user->fromArray(json_decode($user_content, TRUE));
        $db->replaceUser($user);

        $this->setAttribute('user', $user);
        return 'Success';
    }
}
