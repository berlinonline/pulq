<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @category  phpDocumentor
 * @package   Plugin
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Plugin;

/**
 * Layer superclass for the Plugin Component; contains all event
 * dispatching code.
 *
 * @category phpDocumentor
 * @package  Plugin
 * @author   Mike van Riel <mike.vanriel@naenius.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @link     http://phpdoc.org
 */
class PluginAbstract
{
    /** @var \sfEventDispatcher Dispatcher used to send events back and forth */
    protected $event_dispatcher = null;

    /** @var \Zend\Config\Config Configuration object for plugins */
    protected $configuration = null;

    /** @var \Zend\Translator\Adapter\ArrayAdapter Translation object */
    protected $translate = null;

    /**
     * Initialize this object with an Event Dispatcher and Configuration object.
     *
     * @param sfEventDispatcher                     $event_dispatcher
     *     Dispatcher used to handle events.
     * @param \Zend\Config\Config                   $configuration
     *     Configuration object for this object.
     * @param \Zend\Translator\Adapter\ArrayAdapter $translator
     *     Translator object.
     */
    public function __construct(
        $event_dispatcher, $configuration, $translator = null
    ) {
        $this->event_dispatcher = $event_dispatcher;
        $this->configuration    = $configuration;
        $this->translate        = $translator;
    }

    /**
     * Dispatches an event to the Event Dispatcher.
     *
     * This method tries to dispatch an event; if no Event Dispatcher has been
     * set than this method will explicitly not fail and return null.
     *
     * By not failing we make the Event Dispatcher optional and is it easier
     * for people to re-use this component in their own application.
     *
     * @param string   $name      Name of the event to dispatch.
     * @param string[] $arguments Arguments for this event.
     *
     * @throws \phpDocumentor\Plugin\Exception if there is a dispatcher but it
     *  is not of type sfEventDispatcher
     *
     * @return mixed|null
     */
    public function dispatch($name, $arguments)
    {
        if (!$this->event_dispatcher) {
            return null;
        }

        if (!$this->event_dispatcher instanceof \sfEventDispatcher) {
            throw new Exception(
                'Expected the event dispatcher to be an instance of '
                . 'sfEventDispatcher'
            );
        }

        $event = $this->event_dispatcher->notify(
            new \sfEvent($this, $name, $arguments)
        );

        return $event
            ? $event->getReturnValue()
            : null;
    }

    /**
     * Dispatches a logging request.
     *
     * @param string $message  The message to log.
     * @param int    $priority The logging priority, the lower,
     *  the more important. Ranges from 1 to 7
     *
     * @return void
     */
    public function log($message, $priority = 6)
    {
        $this->dispatch(
            'system.log',
            array(
                 'message' => $message,
                 'priority' => $priority
            )
        );
    }

    /**
     * Dispatches a parser error to be logged.
     *
     * @param string   $type      The logging priority as string
     * @param string   $message   The message to log.
     * @param string   $line      The line number where the error occurred..
     * @param string[] $variables an array with message substitution variables.
     *
     * @return void
     */
    public function logParserError($type, $code, $line, $variables = array())
    {
        $message = $this->_($code, $variables);
        $this->log($message, \phpDocumentor\Plugin\Core\Log::ERR);
        $this->dispatch(
            'parser.log',
            array(
                 'type'    => $type,
                 'code'    => $code,
                 'message' => $message,
                 'line'    => $line
            )
        );
    }

    /**
     * Dispatches a logging request to log a debug message.
     *
     * @param string $message The message to log.
     *
     * @return void
     */
    public function debug($message)
    {
        $this->dispatch(
            'system.debug',
            array('message' => $message)
        );
    }

    /**
     * Translates the ID or message in the given language.
     *
     * Translation messages may contain any formatting as used by the php
     * vsprintf function.
     *
     * @param string $message   ID or message to translate.
     * @param array  $variables Variables to use for substitution.
     *
     * @return string
     */
    public function _($message, $variables = array())
    {
        if (!$this->translate) {
            return vsprintf($message, $variables);
        }

        return vsprintf($this->translate->translate($message), $variables);
    }

    /**
     * Returns the configuration for this object.
     *
     * @return \Zend\Config\Config
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Returns the event dispatcher.
     *
     * @return sfEventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->event_dispatcher;
    }

    /**
     * Returns the translation component.
     *
     * @return \Zend\Translator\Adapter\ArrayAdapter|null
     */
    public function getTranslator()
    {
        return $this->translate;
    }

}
