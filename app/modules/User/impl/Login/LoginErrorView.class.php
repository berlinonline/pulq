<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package User
 */
class User_Login_LoginErrorView extends UserBaseView
{
    /**
     * There are only two type of response: UserService_User_Interface or UserService_Status
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     */
    public function executeJson(AgaviRequestDataHolder $rd)
    {
        $user = $this->getAttribute('user');
        if ($user instanceof UserService_Status)
        {
            if ($user->code === 302)
            {
                $parameters = array(
                    'user_id' => $this->getAttribute('user_id'),
                );
                $this->getResponse()->setRedirect($this->getContext()->getRouting()->gen('user.lock', $parameters, array('relative' => false)));
            }
        }
        return parent::executeJson($rd);
    }
}
