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
 * The Default_Error404_Error404SuccessView class provides presentation logic for standard 404 handling.
 *
 * @version         $Id: Error404SuccessView.class.php 554 2011-11-16 11:25:55Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Default
 * @subpackage      Mvc
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class Default_Error404_Error404SuccessView extends DefaultBaseView
{

    /**
     * force error response as text/html
     *
     * @author tay
     * @since 08.10.2011
     *
     * @param AgaviRequestDataHolder $parameters
     *
     * @return AgaviExecutionContainer
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeAny(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreStart
    {
        return $this->createForwardContainer(
            $this->getContainer()->getModuleName(),
            $this->getContainer()->getActionName(),
            NULL, 'html', 'read');
    }

    /**
     * Handle presentation logic for commandline interfaces.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $tpl404 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'action.list';
        $content404 = print_r($this->toArray(),1) . file_get_contents($tpl404);

        $this->getResponse()->setContent($content404);
    }

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
        $this->executeCommon($parameters);
        $this->container->getResponse()->setHttpStatusCode($this->getAttribute('status', 404));
    }


    /**
     * (non-PHPdoc)
     * @see PulqBaseView::executeJson()
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $this->executeCommon($parameters);

        /* @var $response AgaviWebResponse */
        $response = $this->getContainer()->getResponse();
        $response->setContentType('application/json');

        $this->getContainer()->setOutputType($this->getContext()->getController()->getOutputType('json'));

        return json_encode($this->getAttributes());
    }


    /**
     *
     *
     * @param AgaviRequestDataHolder $parameters
     * @return string
     */
    public function executeXml(AgaviRequestDataHolder $parameters)
    {
        $this->executeCommon($parameters);

        $out = '';
        foreach ($this->getAttributes() as $name => $val)
        {
            if (is_scalar($val))
            {
                $out .= sprintf("<%1\$s>%2\$s</%1\$s>\n", $name, htmlspecialchars($val));
            }
        }

        return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<pulq>\n$out</pulq>\n";
    }


    /**
     *
     *
     * @param AgaviRequestDataHolder $parameters
     */
    public function executeCommon(AgaviRequestDataHolder $parameters)
    {
        $title = $this->getAttribute('_title');
        $this->setAttribute('_title', empty($title) ? '404 Not Found' : $title);

        $request = $this->getContext()->getRequest();
        if ($request instanceof AgaviWebRequest) {
            $this->setAttribute('url',$request->getUrl());
        }

        $this->findRelatedAction();

        $this->container->getResponse()->setHttpStatusCode($this->getAttribute('status', 404));
    }

    /**
     * identify related module/action and sets the appropiate atttributes
     *
     * @see AgaviExecutionContainer::createSystemActionForwardContainer()
     */
    protected function findRelatedAction()
    {
        if ($this->hasAttribute('_action'))
        {
            return;
        }
        $container =  $this->getContainer();
        foreach (array('error_404', 'module_disabled', 'secure', 'login', 'unavailable') as $type)
        {
            // @see AgaviExecutionContainer::createSystemActionForwardContainer()
            $ns = 'org.agavi.controller.forwards.'.$type;
            if ($container->hasAttributeNamespace($ns))
            {
                $this->setAttribute('_module', $container->getAttribute('requested_module', $ns));
                $this->setAttribute('_action', $container->getAttribute('requested_action', $ns));
                $exception = $container->getAttribute('exception', $ns);
                if ($exception instanceof Exception)
                {
                    $this->setAttribute('exception', $exception);
                }
                break;
            }
        }
    }
}
