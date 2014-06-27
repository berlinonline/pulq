<?php

namespace Pulq\Agavi\Logging;

use Pulq\Agavi\Logging\Logger;
use Pulq\Agavi\Logging\LoggerManager;

/**
 * Default layout used for log messages. By default an ISO-8601 compatible
 * timestamp format with fractions of a seconds precedes the message.
 *
 * Supported parameters are:
 * - message_format: Layout to use for the logmessage in sprintf syntax:
 *                   - %1$s: timestamp
 *                   - %2$s: log level name
 *                   - %3$s: scope
 *                   - %4$s: message text
 * - timestamp_format: \DateTime supported format for the timestamp. Default is
 *                     "Y-m-d\TH:i:s.uP" ("2013-06-03T13:14:29.857141+00:00").
 * - default_scope: Default string to use for the log message scope if none is
 *                  specified on the \AgaviLoggerMessage itself via the "scope"
 *                  parameter. Defaults to LoggerManager::DEFAULT_MESSAGE_SCOPE.
 */
class LoggerLayout extends \AgaviLoggerLayout
{
    /**
     * Default format for log messages: "[<timestamp>] [<level>] [<scope>] <message text>".
     */
    const DEFAULT_MESSAGE_FORMAT = '[%1$s] [%2$s] [%3$s] %4$s';

    /**
     * ISO-8601 compatible timestamp format with fractions of a second.
     */
    const DEFAULT_TIMESTAMP_FORMAT = 'Y-m-d\TH:i:s.uP';

    /**
     * Format a log message according to the configured message and timestamp
     * format.
     *
     * @param \AgaviLoggerMessage $message instance to log.
     *
     * @return string formatted log message
     */
    public function format(\AgaviLoggerMessage $message)
    {
        $now = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));

        $timestamp = $now->format($this->getParameter('timestamp_format', self::DEFAULT_TIMESTAMP_FORMAT));
        $scope = $this->getParameter('default_scope', $message->getParameter('scope', LoggerManager::DEFAULT_MESSAGE_SCOPE));
        $template = $this->getParameter('message_format', self::DEFAULT_MESSAGE_FORMAT);
        $log_level = Logger::getLevelName($message);

        return sprintf($template, $timestamp, $log_level, $scope, $message->__toString());
    }
}
