<?php

/**
 * The ProjectBaseAction serves as the base action to all actions implemented inside of this project.
 *
 * @version         $Id: ProjectBaseAction.class.php 1013 2012-03-02 21:28:23Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      Agavi/Action
 */
class ProjectBaseAction extends PulqBaseAction
{
    /**
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeRead(AgaviRequestDataHolder $rd)
    {
        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
