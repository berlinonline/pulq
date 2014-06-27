<?php

namespace Pulq\Agavi\Logging\Monolog;

/**
 * Interface that classes should implement that want to return a configured
 * \Monolog\Logger instance for usage by the \Pulq\Agavi\Logging\Logger.
 */
interface IMonologSetup
{
    /**
     * @param \AgaviLoggerAppender $appender instance to use for getting parameters from logging.xml file.
     *
     * @return \Monolog\Logger configured instance with handlers and processors
     */
    public static function getMonologInstance(\AgaviLoggerAppender $appender);
}
