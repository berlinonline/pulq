<?php

/**
 * The ProjectBaseView serves as the base view to all views implemented inside of this project.
 *
 * @version         $Id: ProjectBaseView.class.php 1013 2012-03-02 21:28:23Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      Agavi/View
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class ProjectBaseView extends PulqBaseView
{

    /**
     * HTML-Output is currently not supported, set 406-HTTP-Status
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     */
    public function executeHtml(AgaviRequestDataHolder $rd)
    {
        $this->getResponse()
            ->setHttpStatusCode(406);
        $this->getResponse()
            ->setContent('406 - Not Acceptable: text/html, Accept: application/json');
        $this->getResponse()
            ->setHttpHeader("Content-Type", "application/json");
    }
}
