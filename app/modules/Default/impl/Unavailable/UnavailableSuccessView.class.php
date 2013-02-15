<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * The Default_Unavailable_UnavailableSuccessView class handles the presentation logic
 * required for the %system_actions.unavailable% action.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Default
 * @subpackage      Mvc
 */
class Default_Unavailable_UnavailableSuccessView extends DefaultBaseView 
{
    /**
     * Execute any html related presentation logic and sets up our template attributes.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->setupHtml($parameters);

        // set the title
        $this->setAttribute('_title', $this->translationManager->_('This Application is Unavailable'));

        $this->getResponse()->setHttpStatusCode('503');
    }
    
    /**
     * Prepares and sets our json data on our webresponse.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->getContainer()->getResponse()->setContent(
            json_encode(
                array(
                    'result' => 'error',
                    'message' => 'This application is unavailable.'
                )
            )
        );
    }

    /**
     * Prepares and sets our json data on our console response.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $msg = 'This application is unavailable.' . PHP_EOL .

        $this->getResponse()->setContent($msg);
    }
}

?>