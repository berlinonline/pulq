<?php

class Default_Error404_Error404SuccessView extends DefaultBaseView
{
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
     * Handle 404 errors for commandline interfaces by logging matched routes
     * information and displaying a help message with currently configured
     * routes (including information about pattern, parameters, validation and
     * descriptions) to STDERR with an exit code of 1 for the shell.
     *
     * @param \AgaviRequestDataHolder $request_data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeText(\AgaviRequestDataHolder $request_data) // @codingStandardsIgnoreEnd
    {
        $error_message = '';

        $this->logMatchedRoute();

        $route_names_array = $this->request->getAttribute('matched_routes', 'org.agavi.routing');
        if (empty($route_names_array))
        {
            $error_message .= PHP_EOL . 'No route matched the given command line arguments: ' . $this->routing->getInput() . PHP_EOL;
        }

        $title = $this->controller->getParameter('org.honeybee.error_404.title', $this->getAttribute('org.honeybee.error_404.title'));
        $message = $this->controller->getParameter('org.honeybee.error_404.message', $this->getAttribute('org.honeybee.error_404.message'));
        if (!empty($message) || !empty($title))
        {
            $error_message .= PHP_EOL . 'Details about the error:' . PHP_EOL;
        }

        if (!empty($title))
        {
            $error_message .= 'Title: ' . $title . PHP_EOL;
        }

        if (!empty($message))
        {
            $error_message .= 'Message: ' . $message . PHP_EOL;
        }

        if (!empty($message) || !empty($title))
        {
            $error_message .= PHP_EOL;
        }

        $error_message .= 'The following routes and parameters are available:' . PHP_EOL . PHP_EOL;

        $all_routes = $this->getRoutes();

        // sort routes by value of the pattern field (case-insensitive alphanumeric)
        $all_pattern = array();
        foreach ($all_routes as $key => $route_info)
        {
            $all_pattern[$key] = $route_info['pattern'];
        }
        array_multisort($all_pattern, SORT_NATURAL | SORT_FLAG_CASE, $all_routes);

        // create help with parameters/validation and description for each known route
        foreach ($all_routes as $route)
        {
            $error_message .= '  ' . $route['pattern'] . PHP_EOL;

            if (isset($route['description']))
            {
                $error_message .= '    ' . $route['description'] . PHP_EOL;
            }

            if (!count($route['parameters']))
            {
                $error_message .= PHP_EOL;
                continue;
            }

            foreach ($route['parameters'] as $parameter)
            {
                $has_base_keys = false;

                // set the correct name when the argument has a base
                if (!is_null($parameter['base']))
                {
                    $parameter_name = $parameter['base'];

                    // keys of the base are defined as name by the validator
                    if (!is_null($parameter['name']) && !empty($parameter['name']))
                    {
                        $has_base_keys = true;
                    }
                }
                else
                {
                    $parameter_name = $parameter['name'];
                }

                $error_message .= '    -' . $parameter_name . ': ' . $parameter['class'] . ($parameter['required'] == 'true' ? '' : ' (optional)') . PHP_EOL;

                if ($has_base_keys)
                {
                    $error_message .= '      keys: ' . $parameter['name'] . PHP_EOL;
                }

                // use description parameter from validator if available
                if (isset($parameter['description']))
                {
                    $error_message .= '      ' . $parameter['description'] . PHP_EOL;
                }
            }

            $error_message .= PHP_EOL;
        }

        $error_message .= 'Usage: bin/cli <routename> [parameters]' . PHP_EOL;

        if (!$this->getResponse()->getParameter('append_eol', true))
        {
            $error_message .= PHP_EOL;
        }

        $this->getResponse()->setExitCode(1);

        /*
         * we just send stuff to STDERR as AgaviResponse::sendContent() uses fpassthru which
         * does not allow us to give the handle to Agavi via $rp->setContent() or return $handle
         * notice though, that the shell exit code will still be set correctly
         */
        if (php_sapi_name() === 'cli' && defined('STDERR'))
        {
            fwrite(STDERR, $error_message);
            fclose(STDERR);
        }
        else
        {
            return $error_message;
        }
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
     * Logs matched routes routing information to all debug logs for easier
     * debugging of 404 errors.
     *
     * @return void
     */
    protected function logMatchedRoute()
    {
        $this->findRelatedAction();
        $requested = array();
        foreach (array('_module', '_action') as $name)
        {
            if ($this->getAttribute($name))
            {
                $requested[] = $this->getAttribute($name);
            }
        }
        $origin = empty($requested) ? '' : ' - Requested module/action: ' . join('/', $requested);

        $output_type = $this->getResponse()->getOutputType()->getName();
        $request_method = $this->request->getMethod();

        $uri = '';
        if (php_sapi_name() !== 'cli' && isset($_SERVER['REQUEST_URI']))
        {
            $uri = "for request URI '" . $_SERVER['REQUEST_URI'] . "'";
        }
        else
        {
            $uri = 'for input: ' . $this->routing->getInput();
        }

        $log_message = '';
        $route_names_array = $this->request->getAttribute('matched_routes', 'org.agavi.routing');
        if (!empty($route_names_array))
        {
            $main_route = $this->routing->getRoute(reset($route_names_array));
            $main_module = $main_route['opt']['module'];
            $main_action = $main_route['opt']['action'];
            $log_message = "main module='$main_module' main action='$main_action' output_type='$output_type' request_method='$request_method' matched $uri - matched_routes were:" . implode(', ', $route_names_array) . $origin;
        }
        else
        {
            $log_message = "No route matched (request method '$request_method', output type '$output_type') $uri" . $origin;
        }

        $this->logDebug($log_message);
    }

    /**
     * Recursively get all route information for given action and module name.
     *
     * @author Jan Schütze <jans@dracoblue.de>
     *
     * @param string $parent name of parent route
     * @param string $action name of action
     * @param string $module name of module
     *
     * @return array of routes found with pattern, parameters and description
     */
    protected function getRoutes($parent = null, $action = null, $module = null)
    {
        $routes = array();

        foreach ($this->routing->exportRoutes() as $possible_route)
        {
            if ($possible_route['opt']['parent'] !== $parent)
            {
                continue ;
            }

            if (!$possible_route['opt']['action'])
            {
                $possible_route['opt']['action'] = $action;
            }

            if (!$possible_route['opt']['module'])
            {
                $possible_route['opt']['module'] = $module;
            }

            if ($possible_route['opt']['action'] && $possible_route['opt']['module'])
            {
                $route = array(
                    'pattern' => $possible_route['opt']['reverseStr'],
                    'parameters' => $this->getParametersForActionAndModule($possible_route['opt']['action'], $possible_route['opt']['module'])
                );

                if (isset($possible_route['opt']['parameters']['description']))
                {
                    $route['description'] = $possible_route['opt']['parameters']['description'];
                }

                $routes[] = $route;
            }

            if (count($possible_route['opt']['childs']))
            {
                foreach ($this->getRoutes($possible_route['opt']['name'], $possible_route['opt']['action'], $possible_route['opt']['module']) as $sub_route)
                {
                    $sub_route['pattern'] = $possible_route['opt']['reverseStr'] . $sub_route['pattern'];
                    $routes[] = $sub_route;
                }
            }
        }

        return $routes;
    }

    /**
     * Get validation information from agavi for the given action and module
     * name for the request method 'read'.
     *
     * @author Jan Schütze <jans@dracoblue.de>
     *
     * @param string $action name of action
     * @param string $module name of module
     *
     * @return array of parameters for all registered validators
     */
    protected function getParametersForActionAndModule($action, $module, $method = 'read')
    {
        /*
         * Agavi uses different coding standard, so we ignore the following...
         *
         * @codingStandardsIgnoreStart
         */
        $parameters = array();

        $this->getContext()->getController()->initializeModule($module);

        $validationManager = $this->getContext()->createInstanceFor('validation_manager');
        $validationConfig = \AgaviToolkit::evaluateModuleDirective($module, 'agavi.validate.path', array(
            'moduleName' => $module,
            'actionName' => $action,
        ));

        if (is_readable($validationConfig))
        {
            require(\AgaviConfigCache::checkConfig($validationConfig, $this->getContext()->getName()));
        }

        foreach ($validationManager->getChilds() as $validator)
        {
            $property = new \ReflectionProperty(get_class($validator), 'arguments');
            $property->setAccessible(true);
            $arguments = $property->getValue($validator);
            $parameters[] = array(
                'name' => implode(', ', $arguments),
                'class' => $validator->getParameter('class'),
                'required' => $validator->getParameter('required', 'true'),
                'description' => $validator->getParameter('description', null),
                'base' => $validator->getParameter('base', null)
            );
        }

        /*
         * @codingStandardsIgnoreEnd
         */

        return $parameters;
    }
}
