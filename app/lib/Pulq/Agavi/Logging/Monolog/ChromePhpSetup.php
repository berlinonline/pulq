<?php

namespace Pulq\Agavi\Logging\Monolog;

use Monolog\Logger;
use Monolog\Handler\ChromePHPHandler;

/**
 * Returns a configured \Monolog\Logger instance that logs all messages above
 * DEBUG level via ChromePHP.
 *
 * Supported appender parameters:
 * - minimum_level: Minimum \Monolog\LogLevel to log. Defaults to DEBUG
 * - channel: The channel name to use for logging. Defaults to appender name.
 * - bubble: Boolean value to specify whether messages that are handled should
 *           bubble up the stack or not. Defaults to true.
 */
class ChromePhpSetup implements IMonologSetup
{
    /**
     * @param \AgaviLoggerAppender $appender Agavi logger appender instance to use for \Monolog\Logger instance creation
     *
     * @return \Monolog\Logger with \Monolog\Handler\ChromePHPHandler
     */
    public static function getMonologInstance(\AgaviLoggerAppender $appender)
    {
        // get and define all parameters and their default values
        $minimum_level = $appender->getParameter('minimum_level', Logger::DEBUG);
        $bubble = $appender->getParameter('bubble', true);
        $channel_name = $appender->getParameter('channel', $appender->getParameter('name', 'monolog-default'));

        $logger = new Logger($channel_name);
        $logger->pushHandler(new ChromePHPHandler($minimum_level, $bubble));

        // return the \Monolog\Logger instance to the caller
        return $logger;
    }
}
