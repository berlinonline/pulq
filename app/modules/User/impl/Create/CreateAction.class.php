<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package User
 */
class User_CreateAction extends UserBaseAction
{
    public function executeWrite(AgaviRequestDataHolder $rd)
    {
        $loginname = $rd->getParameter('loginname');
        $user = UserService_User::create()
            ->setLoginname($loginname)
            ->setEmail($rd->getParameter('email'))
            ->setPassword($rd->getParameter('password'))
        ;
        if ($rd->getParameter('realname'))
        {
            $user->setName($rd->getParameter('realname'));
        }
        if ($rd->getParameter('comment'))
        {
            $user->setComment($rd->getParameter('comment'));
        }
        $db = $this->getUserService_Database();
        if ($db->existsLogin($loginname))
        {
            $status = UserService_Status::create(FALSE, 409, "User with loginname '%s' already exists", array($loginname));
            $this->setAttribute('user', $status);
            return 'Error';
        }
        $db->replaceUser($user);
        $this->setAttribute('user', $user);
        return 'Success';
    }
}

?>
