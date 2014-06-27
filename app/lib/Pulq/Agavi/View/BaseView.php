<?php

namespace Pulq\Agavi\View;

/**
 * The BaseView serves as the base view to all views implemented inside of this project.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class BaseView extends \AgaviView
{
    /*
      This is the base view all your application's views should extend.
      This way, you can easily inject new functionality into all of your views.

      One example would be to extend the initialize() method and assign commonly
      used objects such as the request as protected class members.

      Even if you don't need any of the above and this class remains empty, it is
      strongly recommended you keep it. There shall come the day where you are
      happy to have it this way ;)

      This default implementation throws an exception if execute() is called,
      which means that no execute*() method specific to the current output type
      was declared in your view, and no such method exists in this class either.

      It is of course highly recommended that you change the names of any default
      base classes to carry a prefix and have an overall meaningful naming scheme.
      You can enable the usage of the respective custom template files via
      build.properties settings. Also, keep in mind that you can define templates
      for specific modules in case you require this.
     */

    /**
     * Name of the default layout to use for slots.
     */
    const DEFAULT_SLOT_LAYOUT_NAME = 'slot';

    /**
     * Holds a reference to the current routing object.
     *
     * @var         AgaviRouting
     */
    protected $routing;

    /**
     * Holds a reference to the current request object.
     *
     * @var         AgaviRequest
     */
    protected $request;

    /**
     * Holds a reference to the translation manager.
     *
     * @var         AgaviTranslationManager
     */
    protected $translationManager;

    /**
     * Holds a reference to the user for the current session.
     *
     * @var         AgaviUser
     */
    protected $user;

    public function initialize(\AgaviExecutionContainer $container)
    {
        parent::initialize($container);
        
        $this->controller = $this->getContext()->getController();
        $this->routing = $this->getContext()->getRouting();
        $this->request = $this->getContext()->getRequest();
        $this->translationManager = $this->getContext()->getTranslationManager();
        $this->user = $this->getContext()->getUser();
    }

    /**
     * If developers try to use the execute method in views instead of creating
     * an output type specific handler they will get a fatal error. If they call
     * this method directly we try to help them with an exception.
     *
     * @param \AgaviRequestDataHolder $request_data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public final function execute(\AgaviRequestDataHolder $request_data) // @codingStandardsIgnoreEnd
    {
        throw new \AgaviViewException(
            sprintf(
                'There should be no "execute()" method in "%1$s". Views deal ' .
                'with output types and should therefore implement specific ' .
                '"execute<OutputTypeName>()" methods. It is recommended that ' .
                'you either implement "execute%3$s()" for the current output type ' .
                '"%2$s" and all other supported output types in each of your views ' .
                'or implement more general fallbacks in the module\'s or applications\'s base views (e.g. "%4$s").',
                get_class($this),
                $this->container->getOutputType()->getName(),
                ucfirst(strtolower($this->container->getOutputType()->getName())),
                get_class()
            )
        );
    }

    /**
     * Handles non-existing methods. This includes mainly the not implemented
     * handling of certain output types.
     *
     * @param string $method_name
     * @param array $arguments
     *
     * @throws \AgaviViewException with different messages
     */
    public function __call($method_name, $arguments)
    {
        if (preg_match('~^(execute|setup)([A-Za-z_]+)$~', $method_name, $matches))
        {
            $this->throwOutputTypeNotImplementedException();
        }

        throw new \AgaviViewException(
            sprintf(
                'The view "%1$s" does not implement an "%2$s()" method. Please ' .
                'implement "%1$s::%2$s()" or handle this situation in one of the base views (e.g. "%3$s").',
                get_class($this),
                $method_name,
                get_class()
            )
        );
    }

    /**
     * Convenience method for setting up the correct html layout.
     *
     * @param       AgaviRequestDataHolder $parameters
     * @param       string $layoutName
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function setupHtml(\AgaviRequestDataHolder $parameters, $layoutName = NULL) // @codingStandardsIgnoreEnd
    {
        if ($layoutName === NULL && $this->getContainer()->getParameter('is_slot', FALSE))
        {
            $layoutName = self::DEFAULT_SLOT_LAYOUT_NAME;
        }
        else
        {
            // set a default title just to avoid warnings
            $this->setAttribute('_title', 'No Title');
        }

        $this->loadLayout($layoutName);
    }

    /**
     * Convenience method for throwing an exception saying,
     * that the current output type is not implemented for this view instance.
     *
     * @throws      AgaviViewException
     */
    protected function throwOutputPutTypeNotImplementedException()
    {
        throw new \AgaviViewException(
            sprintf(
                'The View "%1$s" does not implement an "execute%3$s()" method to serve ' .
                'the Output Type "%2$s". It is recommended that you change the code of ' .
                'the method "execute%3$s()" in the base View "%4$s" that is throwing ' .
                'this exception to deal with this situation in a more appropriate ' .
                'way, for example by forwarding to the default 404 error action, or by ' .
                'showing some other meaningful error message to the user which explains ' .
                'that the operation was unsuccessful beacuse the desired Output Type is ' .
                'not implemented.',
                get_class($this),
                $this->container->getOutputType()->getName(),
                ucfirst(
                    strtolower($this->container->getOutputType()->getName())
                ),
                get_class()
            )
        );
    }

    /**
     * Return any reported validation error messages from our validation manager.
     *
     * @return      array
     */
    protected function getErrorMessages()
    {
        $errors = array();

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[] = $errMsg['message'];
        }

        foreach ($this->getAttribute('errors', array()) as $error)
        {
            $errors[] = $error;
        }

        return $errors;
    }

    /**
     * Sets the breadcrumb URLs as a view attribute. The routes for this are set in getBreadCrumbRoutes.
     */
    protected function setBreadCrumbs()
    {
        $routes = $this->getBreadCrumbRoutes();
        $ro = $this->getContext()->getRouting();

        $urls = array();

        foreach ($routes as $name => $route)
        {
            $urls[$name] = $ro->gen(
                $route['route'],
                isset($route['parameters']) ? $route['parameters'] : array()
            );
        }

        $this->setAttribute('_breadcrumbs', $urls);
    }

    /**
     * Returns an array of routes, each in the form of array('route' => 'foo.bar', paramaters => array(...))
     */
    protected function getBreadCrumbRoutes()
    {
        return array();
    }

    public function getLoggerName()
    {
        return 'default';
    }

    protected function log($level, $args) {
        $this->getContext()->getLoggerManager()->logTo(
            $this->getLoggerName(),
            $level,
            get_class($this),
            $args
        );
    }

    public function logTrace()
    {
        $this->log(\AgaviLogger::TRACE, func_get_args());
    }

    public function logDebug()
    {
        $this->log(\AgaviLogger::DEBUG, func_get_args());
    }

    public function logInfo()
    {
        $this->log(\AgaviLogger::INFO, func_get_args());
    }

    public function logNotice()
    {
        $this->log(\AgaviLogger::NOTICE, func_get_args());
    }

    public function logWarning()
    {
        $this->log(\AgaviLogger::WARNING, func_get_args());
    }

    public function logError()
    {
        $this->log(\AgaviLogger::ERROR, func_get_args());
    }

    public function logCritical()
    {
        $this->log(\AgaviLogger::CRITICAL, func_get_args());
    }

    public function logAlert()
    {
        $this->log(\AgaviLogger::ALERT, func_get_args());
    }

    public function logEmergency()
    {
        $this->log(\AgaviLogger::EMERGENCY, func_get_args());
    }
}
