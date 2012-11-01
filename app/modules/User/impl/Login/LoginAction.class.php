<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package User
 */
class User_LoginAction extends UserBaseAction
{


    /**
     * Handles the Read request method.
     */
    public function executeRead (AgaviRequestDataHolder $rd)
    {
        $auth = $rd->getParameter('auth');
        $view = 'Success';
        try
        {
            $user = $this->fetchUserByAuth($auth);
            $this->setAttribute('user_id', $user->getId());
        }
        catch (UserService_Exception_Found $exception)
        {
            $user = UserService_Status::create(FALSE, 404, "User not found");
            $view = 'Error';
        }
        $this->setAttribute('user', $user);
        return $view;
    }


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
        $auth = $rd->getParameter('auth');
        $password = $rd->getParameter('password');
        $view = 'Success';
        try
        {
            $user = $this->fetchUserByAuth($auth);
            $this->setAttribute('user_id', $user->getId());
        }
        catch (UserService_Exception_Found $exception)
        {
            $user = UserService_Status::create(FALSE, 404, "User not found");
            $view = 'Error';
        }
        if ($user instanceof UserService_User)
        {
            if (!$user->verifyPassword($password))
            {
                sleep(1);
                $user = UserService_Status::create(FALSE, 403, "Password mismatch");
                $view = 'Error';
            }
            elseif ($user->isLocked())
            {
                $user = UserService_Status::create(FALSE, 302, "User is locked");
                $view = 'Error';
            }
        }
        $this->setAttribute('user', $user);
        return $view;
    }


    /**
      * Fetch user by auth string
      *
      * @param String $auth
      * @return UserService_User_Interface
      */
    public function fetchUserByAuth ($auth)
    {
        $db = $this->getUserService_Database();
        if (preg_match('#^\d+$#', $auth))
        {
            $user = $db->fetchUserById($auth);
        }
        elseif (preg_match('#.+@.+\.#', $auth))
        {
            $user = $db->fetchUserByMail($auth);
        }
        else
        {
            $user = $db->fetchUserByLoginname($auth);
        }
        return $user;
    }

}