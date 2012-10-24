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
 * The Default_Index_IndexSuccessView class provides presentation logic for the %system_actions.default% action.
 *
 * @version         $Id: IndexSuccessView.class.php 1181 2012-05-14 10:02:48Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Default
 * @subpackage      Mvc
 */
class Default_Index_IndexSuccessView extends DefaultBaseView
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
        $this->setAttribute('_title', $this->translationManager->_('Welcome to a Pulq based web frontend.'));
    }

    /**
     * (non-PHPdoc)
     * @see PulqBaseView::executeJson()
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        return json_encode(array('_title' => $this->translationManager->_('Welcome to a Pulq based web frontend.')));
    }


    /**
     * Execute any XML related presentation logic and sets up our template attributes.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeXml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $title = $this->translationManager->_('Welcome to a Pulq based web frontend.');
        return <<<EOT
<?xml version="1.0" encoding="UTF-8" ?>
<pulq>
<title>$title</title>
</pulq>
EOT;
    }
}
