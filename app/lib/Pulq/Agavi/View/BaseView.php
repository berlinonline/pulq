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

    /**
     * Initialize the view thereby setting up our members.
     *
     * @param       AgaviExecutionContainer $container
     */
    public function initialize(\AgaviExecutionContainer $container)
    {
        parent::initialize($container);

        $this->routing = $this->getContext()->getRouting();
        $this->request = $this->getContext()->getRequest();
        $this->translationManager = $this->getContext()->getTranslationManager();
        $this->user = $this->getContext()->getUser();
    }

    /**
     * If no output type specfic execute* method could be found on our current
     * concrete implemenation, then we will throw an exception letting the dev know.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public final function execute(\AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->throwOutputPutTypeNotImplementedException();
    }

    /**
     * If this method is called someone has missed to provide html view support
     * for the current action.
     * Let them know ^^
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(\AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->throwOutputPutTypeNotImplementedException();
    }

    /**
     * If this method is called someone has missed to provide json view support
     * for the current action.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(\AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->throwOutputPutTypeNotImplementedException();
    }

    /**
     * If this method is called someone has missed to provide text(console) view support
     * for the current action.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeText(\AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->throwOutputPutTypeNotImplementedException();
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
}
