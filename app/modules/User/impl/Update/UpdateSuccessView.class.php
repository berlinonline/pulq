<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package User
 */
class User_Update_UpdateSuccessView extends UserBaseView
{
    /**
     * Standard status code is 302
     *
     * @param AgaviRequestDataHolder $parameters
     *
     */
    public function executeJson(AgaviRequestDataHolder $rd)
    {
        $user_id = $rd->getParameter('user_id');
        $this->getResponse()->setHttpStatusCode(302);
        $parameters = array(
            'user_id' => $user_id,
        );
        $this->getResponse()->setRedirect($this->getContext()->getRouting()->gen('user.read', $parameters, array('relative' => false)));
        return parent::executeJson($rd);
    }
}
