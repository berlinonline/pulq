<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package User
 */
class User_Login_LoginSuccessView extends UserBaseView
{
    /**
     * Standard status code is 200
     *
     * @param AgaviRequestDataHolder $parameters
     *
     */
    public function executeJson(AgaviRequestDataHolder $rd)
    {
        $user_id = $this->getAttribute('user_id');
        $parameters = array(
            'user_id' => $user_id,
        );
        $redirect = $this->getContext()->getRouting()->gen('user.read', $parameters, array('relative' => false));
        //$this->getResponse()->setHttpHeader('Location', $redirect);
        $this->getResponse()->setHttpStatusCode(200);
        return parent::executeJson($rd);
    }
}
