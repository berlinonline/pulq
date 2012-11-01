<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package User
 */
class User_Create_CreateSuccessView extends UserBaseView
{
    /**
     * Standard status code is 201
     *
     * @param AgaviRequestDataHolder $parameters
     *
     */
    public function executeJson(AgaviRequestDataHolder $rd)
    {
        $this->getResponse()->setHttpStatusCode(201);
        return parent::executeJson($rd);
    }
}
