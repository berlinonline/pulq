<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id: LockSuccessView.class.php 21 2012-03-08 13:51:57Z mfischer $
 * @package User
 */
class User_Lock_LockSuccessView extends UserBaseView
{
    /**
     * Standard status code is 201
     *
     * @param AgaviRequestDataHolder $parameters
     *
     */
    public function executeJson(AgaviRequestDataHolder $rd)
    {
        $user = $this->getAttribute('user');
        if ($user instanceof UserService_User_Interface)
        {
            $this->getResponse()->setHttpHeader('Expires', $user->getExpiresTime());
        }
        return parent::executeJson($rd);
    }
}
