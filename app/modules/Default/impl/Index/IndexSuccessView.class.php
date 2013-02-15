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

use Honeybee\Core\Dat0r\ModuleService;

/**
 * The Default_Index_IndexSuccessView class provides presentation logic for the %system_actions.default% action.
 *
 * @version         $Id$
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
        $routing = $this->getContext()->getRouting();
        $service = new ModuleService();

        $modules = array();
        foreach ($service->getModules() as $module)
        {
            $modules[$module->getName()] = array(
                'list_link' => $routing->gen($module->getOption('prefix') . '.list')
            );
        }

        $this->setAttribute('modules', $modules);
        $this->setAttribute('_title', $this->translationManager->_('Welcome to the Honeybee web frontend.'));

        $this->setBreadcrumb();
    }

    protected function setBreadcrumb()
    {
        $this->getContext()->getUser()->setAttribute('breadcrumbs', array(), 'honeybee.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('modulecrumb', NULL, 'honeybee.breadcrumbs');
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
                    'message' => 'Welcome to the Honeybee JSON API.'
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
        $msg = 'Welcome to the Honeybee CLI Interface.' . PHP_EOL;

        $this->getResponse()->setContent($msg);
    }
}

?>
