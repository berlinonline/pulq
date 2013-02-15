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
 * @version         $Id$
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
     * @param AgaviRequestDataHolder $parameters
     * @return AgaviExecutionContainer
     */
    public function executePng(AgaviRequestDataHolder $parameters)
    {
        return $this->executeAny($parameters);
    }

    /**
     * force error response as text/html
     *
     * @author tay
     * @since 08.10.2011
     * @param AgaviRequestDataHolder $parameters
     * @return AgaviExecutionContainer
     */
    public function executeSvg(AgaviRequestDataHolder $parameters)
    {
        return $this->executeAny($parameters);
    }

    /**
     * force error response as text/html
     *
     * @author tay
     * @since 08.10.2011
     * @param AgaviRequestDataHolder $parameters
     * @return AgaviExecutionContainer
     */
    public function executeRss(AgaviRequestDataHolder $parameters)
    {
        return $this->executeAny($parameters);
    }

    /**
     * force error response as text/html
     *
     * @author tay
     * @since 08.10.2011
     * @param AgaviRequestDataHolder $parameters
     * @return AgaviExecutionContainer
     */
    public function executeKml(AgaviRequestDataHolder $parameters)
    {
        return $this->executeAny($parameters);
    }

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
    public function executeXml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $response = $this->getContainer()->getResponse();
        /* @var $response AgaviWebResponse */
        $response->setHttpStatusCode($this->getAttribute('status', 404));

        $this->findRelatedAction();
        $this->logError();

        return '<?xml version="1.0" encoding="UTF-8"?><error>'.$this->encodeXml($this->toArray()).'</error>';
    }

    /**
     * Encode the given array to a simple xml
     *
     * array keys are converted to tag names and content is convertert to pcdata. Array values must be strings
     * or array.
     *
     * @param array $data array to encode
     * @return string xml
     */
    protected function encodeXml(array $data)
    {
        $inner = '';
        foreach ($data as $key => $val)
        {
            if (is_array($val))
            {
                $inner .= sprintf("<%s>\n2$s</%1$s>\n", $key, $this->encodeXml($val));
            }
            else
            {
                $inner .= sprintf("<%1$s>%<![CDATA[2$s]]></%1$s>\n", $key, htmlspecialchars($val));
            }
        }
        return $inner;
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
        $this->getContext()->getRequest()->setAttribute('is_error_page', TRUE);
        $this->setupHtml($parameters);

        $title = $this->getAttribute('_title');
        $this->setAttribute('_title', empty($title) ? '404 Not Found' : $title);

        $request = $this->getContext()->getRequest();
        if ($request instanceof AgaviWebRequest) {
             $this->setAttribute('url',$request->getUrl());
        }

        $this->findRelatedAction();
        $this->logError();

        /* @var $response AgaviWebResponse */
        $response = $this->getContainer()->getResponse();
        $response->setContentType('text/html');

        $this->getContext()->getUser()->setAttribute('breadcrumbs', array(), 'honeybee.breadcrumbs');
        $this->container->getResponse()->setHttpStatusCode($this->getAttribute('status', 404));
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
        $response = $this->getContainer()->getResponse();
        /* @var $response AgaviWebResponse */
        $response->setHttpStatusCode($this->getAttribute('status', 404));
        $response->setContentType('application/json');

        $this->findRelatedAction();
        $this->logError();
        return json_encode($this->toArray());
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

    /**
     * get attributes as array for json and xml output
     *
     * @return array
     */
    protected function toArray()
    {
        $result = array(
            'result' => 'error',
            'message' => $this->getAttribute('_title', '404 Not Found'),
            'module' => $this->getAttribute('_module', NULL),
            'action' => $this->getAttribute('_action', NULL),
            'method' => $this->getAttribute('method', NULL)
        );

        $request = $this->getContext()->getRequest();
        if ($request instanceof AgaviWebRequest) {
            $result['url'] = $request->getUrl();
        }

        $errors = array();
        $exception = $this->getAttribute('exception', NULL);
        if ($exception instanceof Exception)
        {
            while ($exception->getPrevious())
            {
                $exception = $exception->getPrevious();
            }
            $errors[] = array('arguments' => get_class($exception), 'message' => $exception->getMessage());
        }
        foreach ($this->getAttribute('errors', array()) as $error)
        {
            if ($error instanceof AgaviValidationError)
            {
                $errors[] = array('arguments' => implode(',',$error->getFields()), 'message' => $error->getMessage());
            }
        }
        $result['errors'] = $errors;

        return $result;
    }


    /**
     * Log message to agavi log system
     */
    protected function logError()
    {
        $logger = $this->getContext()->getLoggerManager();

        $where = array();
        foreach (array('_module', '_action', '_method') as $name)
        {
            if ($this->getAttribute($name))
            {
                $where[] = $this->getAttribute($name);
            }
        }
        $message = empty($where) ? '' : join('/', $where).' :: ';

        $exception = $this->getAttribute('exception');
        if ($exception instanceof Exception)
        {
            while ($exception->getPrevious())
            {
                $exception = $exception->getPrevious();
            }
            $message .= $exception->getMessage();
            if ($logger)
            {
                $logger->log($exception->__toString(), AgaviILogger::INFO);
            }
        }

        if (! $this->getContainer()->getParameter('is_slot'))
        {
            $message .= sprintf("'%s' :: %s", $this->getAttribute('url'), $this->getAttribute('_title'));
        }
        else
        {
            $message .= $this->getAttribute('_title');
        }

        if ($logger)
        {
            $logger->log($message, AgaviILogger::ERROR);
        }
    }
}

?>