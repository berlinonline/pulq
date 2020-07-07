<?php

namespace Pulq\Agavi\Logging;

use Pulq\Agavi\Logging\Logger;
use Pulq\Agavi\Logging\Psr3Logger;

/**
 * Extends \AgaviLoggerManager with log level specific convenience methods.
 */
class LoggerManager extends \AgaviLoggerManager implements ILogger//, \Psr\Log\LoggerAwareInterface
{
    private static $pid;

    /**
     * @var string to use as default scope in log message
     */
    const DEFAULT_MESSAGE_SCOPE = 'Pulq';

    /**
     * Logs a TRACE level message composited from all given arguments (as a
     * concatenated string) to all loggers interested in TRACE level messages.
     */
    public function logTrace()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::TRACE, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    /**
     * Logs a DEBUG level message composited from all given arguments (as a
     * concatenated string) to all loggers interested in DEBUG level messages.
     */
    public function logDebug()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::DEBUG, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    /**
     * Logs a INFO level message composited from all given arguments (as a
     * concatenated string) to all loggers interested in INFO level messages.
     */
    public function logInfo()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::INFO, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    /**
     * Logs a NOTICE level message composited from all given arguments (as a
     * concatenated string) to all loggers interested in NOTICE level messages.
     */
    public function logNotice()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::NOTICE, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    /**
     * Logs a WARNING level message composited from all given arguments (as a
     * concatenated string) to all loggers interested in WARNING level messages.
     */
    public function logWarning()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::WARNING, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    /**
     * Logs an ERROR level message composited from all given arguments (as a
     * concatenated string) to all loggers interested in ERROR level messages.
     */
    public function logError()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::ERROR, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    /**
     * Logs a CRITICAL level message composited from all given arguments (as a
     * concatenated string) to all loggers interested in CRITICAL level messages.
     */
    public function logCritical()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::CRITICAL, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    /**
     * Logs an ALERT level message composited from all given arguments (as a
     * concatenated string) to all loggers interested in ALERT level messages.
     */
    public function logAlert()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::ALERT, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    /**
     * Logs an EMERGENCY level message composited from all given arguments (as a
     * concatenated string) to all loggers interested in EMERGENCY level messages.
     */
    public function logEmergency()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::EMERGENCY, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    /**
     * Logs all given log message parts as a string to the given logger with
     * the specifed log level and scope name.
     *
     * @param string $logger_name log channel name (logger name defined in logging.xml), NULL should fallback to the default logger
     * @param int $log_level log level to use for logger message creation
     * @param string $scope string or object implementing __toString() for scope of log message (e.g. callee class name or sub channel name)
     * @param mixed $log_message_parts string or object to log or array that contains log message parts ($log_message_parts or its array entries need to be of known types or implement __toString())
     *
     * @return void
     *
     * @throws \InvalidArgumentException when there's no logger configured for the given logger name
     */
    public function logTo($logger_name = null, $log_level = \AgaviLogger::INFO, $scope = self::DEFAULT_MESSAGE_SCOPE, $log_message_parts = "")
    {
        if (!\AgaviConfig::get('core.use_logging', true))
        {
            return;
        }

        /* @var $logger Pulq\Agavi\Logging\Logger */
        $logger = $this->getLogger($logger_name);

        if (!$logger)
        {
            throw new \InvalidArgumentException("Can't find logger with name '$logger_name'. Please specify another name or define the logger in the logging.xml file.");
        }

        $logger_message = $this->createLoggerMessage($log_level, $scope, $log_message_parts);

        $logger->log($logger_message);
    }

    /**
     * Logs the given log message parts as a string to ALL loggers that are
     * interested in a message of that log level.
     *
     * @param int $log_level log level of the message
     * @param string $scope string for scope of log message (e.g. callee class name or sub channel name)
     * @param mixed $log_message_parts string or object to log or array that contains log message parts ($log_message_parts or its array entries need to be of known types or implement __toString())
     *
     * @return void
     *
     * @throws \InvalidArgumentException when there's no logger configured for the given logger name
     */
    public function logToAll($log_level = \AgaviLogger::INFO, $scope = self::DEFAULT_MESSAGE_SCOPE, $log_message_parts = "")
    {
        if (!\AgaviConfig::get('core.use_logging', true))
        {
            return;
        }

        $class_name = $this->getDefaultMessageClass();
        $logger_message = new $class_name();
        $logger_message->setLevel($log_level);
        $logger_message->setParameter('scope', trim($scope));

        $text = '';
        if (is_array($log_message_parts))
        {
            // analyse log_message_parts to get nicely formatted strings for known classes etc.
            $text_message_parts = array();
            foreach ($log_message_parts as $log_message_part)
            {
                $text_message_parts[] = self::getAsString($log_message_part);
            }

            $text = implode(' ', $text_message_parts);
        }
        else
        {
            $text = self::getAsString($log_message_parts);
        }

        $logger_message->setMessage($text);

        // log this message to ALL loggers
        $this->log($logger_message);
    }

    /**
     * Creates and returns an \AgaviLoggerMessage instance. To return a
     * different class set the default_message_class parameter in the
     * for the logger_manager entry in the factories.xml file.
     *
     * The log message parts need to be either strings, arrays or objects
     * implementing __toString(). Instances of the following classes are
     * treated in a special way automatically:
     * - \Exception
     * - Pulq\Core\Dat0r\Module - name of module
     * - Pulq\Core\Dat0r\Document - uuid of document
     * - \AgaviValidationManager - messages of all incidents
     * - \DateTime - ISO-8601 representation
     *
     * @see self::getAsString()
     *
     * If the log message consists of exactly two parts while the first is a
     * string and the second an associative array it is used as a PSR-3
     * compatible call and templating with the given context array according to
     * PSR-3 via {placeholders} is supported. This usage still includes getting
     * supported known types as strings. The given Agavi log level is being
     * converted to a PSR-3 compatible log level and the given scope is added
     * as a parameter to the message.
     *
     * @param int $log_level Agavi log level to use
     * @param string $scope name for the scope to use
     * @param mixed $log_message_parts string or object to log or array that contains log message parts ($log_message_parts or its array entries need to be of known types or implement __toString())
     *
     * @return \AgaviLoggerMessage
     *
     * @throws \InvalidArgumentException if __toString() is not callable on a log message part object
     */
    public function createLoggerMessage($log_level, $scope = self::DEFAULT_MESSAGE_SCOPE, $log_message_parts = "")
    {
        $text_message_parts = array();
        $class_name = $this->getDefaultMessageClass();
        $logger_message = new $class_name();
        $logger_message->setLevel($log_level);
        $logger_message->setParameter('scope', trim($scope));

        // might be a PSR-3 compatible log call with templated message and context array
        if ((\is_array($log_message_parts) || $log_message_parts instanceof \Countable) && 2 === count($log_message_parts)
            && is_string($log_message_parts[0]) && self::isAssoc($log_message_parts[1])
            && (false !== strpos($log_message_parts[0], '{')))
        {
            $logger_message->setParameter('psr3.context', $log_message_parts[1]);
            $logger_message->setLevel(Logger::getAgaviLogLevel($log_level));
            $logger_message->setMessage(Psr3Logger::replacePlaceholders($log_message_parts[0], $log_message_parts[1]));
            if (isset($log_message_parts[1]['scope']))
            {
                $logger_message->setParameter('scope', $log_message_parts[1]['scope']);
            }
            return $logger_message;
        }

        $text = '';
        if (is_array($log_message_parts))
        {
            // analyse log_message_parts to get nicely formatted strings for known classes etc.
            $text_message_parts = array();
            foreach ($log_message_parts as $log_message_part)
            {
                $text_message_parts[] = self::getAsString($log_message_part);
            }

            $text = implode(' ', $text_message_parts);
        }
        else
        {
            $text = self::getAsString($log_message_parts);
        }

        $logger_message->setMessage($text);

        return $logger_message;
    }

    /**
     * Logs all given log message parts as string to the given logger with the
     * specifed log level and scope name.
     *
     * @param string $logger_name log channel name (logger name defined in logging.xml)
     * @param int $log_level log level to use for logger message creation
     * @param string $scope string or object implementing __toString() for scope of log message (e.g. callee class name or sub channel name)
     * @param mixed $log_message_parts string or object to log or array that contains log message parts ($log_message_parts or its array entries need to be of known types or implement __toString())
     *
     * @return void
     *
     * @throws \InvalidArgumentException when there's no logger configured for the given logger name
     */
    public static function logLoggerAndLevel($logger_name = null, $log_level = \AgaviLogger::INFO, $scope = self::DEFAULT_MESSAGE_SCOPE, $log_message_parts = "")
    {
        if (!\AgaviConfig::get('core.use_logging', true))
        {
            return;
        }

        /* @var $agavi_context \AgaviContext */
        $agavi_context = \AgaviContext::getInstance();

        /* @var $logger_manager Pulq\Agavi\Logging\LoggerManager */
        $logger_manager = $agavi_context->getLoggerManager();

        /* @var $logger Pulq\Agavi\Logging\Logger */
        $logger = $logger_manager->getLogger($logger_name);

        if (!$logger)
        {
            throw new \InvalidArgumentException("Can't find logger with name '$logger_name'. Please specify another name or define the logger in the logging.xml file.");
        }

        $logger_message = $logger_manager->createLoggerMessage($log_level, $scope, $log_message_parts);

        $logger->log($logger_message);
    }

    /**
     * Returns a string representation for the given argument. Specifically
     * handles known types like exceptions, ValidationManager instances or
     * Pulq Module and Document instances.
     *
     * @param mixed $log_message_part object, array or string to create textual representation for
     *
     * @return string for the given log message part
     */
    public static function getAsString($log_message_part)
    {
        if ($log_message_part instanceof \Exception)
        {
            return self::getExceptionAsString($log_message_part);
        }
        elseif (is_object($log_message_part))
        {
            return self::getObjectAsString($log_message_part);
        }
        elseif (is_array($log_message_part))
        {
            return print_r($log_message_part, true);
        }
        elseif (is_resource($log_message_part))
        {
            return (string) $log_message_part;
        }

        return (string) $log_message_part;
    }

    /**
     * Returns a string with exception message enhanced by various information
     * like Agavi and PHP version, timestamp, request and routing information.
     *
     * @param \Exception $exception exception to create a log message string for
     *
     * @return string with exception message and further information
     */
    public static function getExceptionAsString(\Exception $exception)
    {
        $extra = array();

        $agavi_context = \AgaviContext::getInstance();

        $remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $remote_addr .= isset($_SERVER['REMOTE_PORT']) ? ':' . $_SERVER['REMOTE_PORT'] : '';
        $server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        $server_name .= isset($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : '';
        $server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $server_addr .= isset($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : '';

        $extra['Timestamp'] = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)))->format('Y-m-d\TH:i:s.uP');
        $extra['Application Name'] = \AgaviConfig::get('core.app_name');
        $extra['Agavi Context'] = $agavi_context->getName();
        $extra['Agavi Environment'] = \AgaviConfig::get('core.environment');
        $extra['Agavi Version'] = \AgaviConfig::get('agavi.version');
        $extra['PHP Version'] = phpversion();
        $extra['System'] = php_uname();
        $extra['Process ID'] = getmypid();

        $ssh_connection = getenv('SSH_CONNECTION');
        if (!empty($ssh_connection))
        {
            $extra['SSH Connection'] = $ssh_connection;
        }

        $user = getenv('USER');
        if (!empty($user))
        {
            $extra['CLI User'] = $user;
            $extra['CLI Home'] = getenv('HOME');
        }

        $extra['Memory Usage'] = self::formatBytes(memory_get_usage(true));
        $extra['Memory Peak Usage'] = self::formatBytes(memory_get_peak_usage(true));

        if (!empty($_SERVER['SERVER_SOFTWARE']))
        {
            $extra['Server Software'] = $_SERVER['SERVER_SOFTWARE'];
        }

        if (!empty($remote_addr))
        {
            $extra['Remote Address'] = $remote_addr;
        }

        if (!empty($_SERVER['X_FORWARDED_FOR']))
        {
            $extra['X-Forwarded-For'] = $_SERVER['X_FORWARDED_FOR'];
        }

        if (!empty($server_name))
        {
            $extra['Server Name'] = $server_name;
        }

        if (!empty($server_addr))
        {
            $extra['Server Address'] = $server_addr;
        }

        if (null !== ($request = $agavi_context->getRequest()))
        {
            if ($request instanceof \AgaviWebRequest)
            {
                $extra['Request URL'] = $request->getUrl();
                $extra['Request Method'] = $request->getMethod();
            }
            elseif ($request instanceof \AgaviConsoleRequest)
            {
                $extra['Input'] = $request->getInput();
            }

            $matched_routes = $request->getAttribute('matched_routes', 'org.agavi.routing');

            if ($matched_routes)
            {
                $extra['Matched Routes (' . count($matched_routes) . ')'] = implode(', ', $matched_routes);
            }

            if (!$request->isLocked())
            {
                $extra['Parameter Names (Request)'] = implode(', ', array_keys($request->getRequestData()->getParameters()));
            }
        }

        foreach ($extra as $key => $value)
        {
            $message_parts[] = str_pad(' ', 30 - strlen($key)) . $key . ': ' . $value;
        }

        return (string) $exception . PHP_EOL . implode(PHP_EOL, $message_parts);
    }

    /**
     * Returns a string for the given object enhanced by various information if
     * the object is of a known type like \AgaviValidationManager, Pulq
     * Module or Document. The given object should implement a `__toString()`
     * method as otherwise the json representation might be empty.
     *
     * @param mixed $obj object to create a log message string for
     *
     * @return string with object representation
     */
    public static function getObjectAsString($obj)
    {
        if ($obj instanceof BaseDataObject)
        {
            return $this->getObjectAsString($obj->toArray());
        }
        elseif ($obj instanceof \DateTime)
        {
            return $obj->format('c');
        }
        elseif ($obj instanceof \AgaviValidationManager)
        {
            $validation_messages = array();
            foreach ($obj->getErrorMessages() as $incident)
            {
                if (!empty($incident['message']))
                {
                    $validation_messages[] = $incident['message'];
                }
            }

            return 'Validation Errors (' . implode(', ', $validation_messages) . ')';
        }
        elseif (is_callable(array($obj, '__toString')))
        {
            return $obj->__toString();
        }
        else
        {
            return json_encode($obj);
        }
    }

    /**
     * @return array with additional logging information as key => value
     */
    public static function getExtraInformation()
    {
        $agavi_context = \AgaviContext::getInstance();

        $request_uri = '';
        if (php_sapi_name() !== 'cli' && isset($_SERVER['REQUEST_URI']))
        {
            $request_uri = $_SERVER['REQUEST_URI'];
        }
        else
        {
            $request_uri = $agavi_context->getRouting()->getInput();
        }

        $matched_module_and_action = '';
        $matched_routes = '';
        $route_names_array = $agavi_context->getRequest()->getAttribute('matched_routes', 'org.agavi.routing');
        if (!empty($route_names_array))
        {
            $main_route = $agavi_context->getRouting()->getRoute(reset($route_names_array));
            $matched_module_and_action = $main_route['opt']['module'] . '/' . $main_route['opt']['action'];
            $matched_routes = implode(', ', $route_names_array);
        }

        // the first container is the one returned from the \AgaviRouting::execute() call
        $first_container =  $agavi_context->getRouting()->getContainer();
        $first_output_type = $first_container->getOutputType()->getName();
        $first_module_action_and_view = $first_container->getModuleName() . '/' . $first_container->getActionName() . ' => ' . $first_container->getViewModuleName() . '/' . $first_container->getViewName();

        $server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        $server_name .= isset($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : '';

        $server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $server_addr .= isset($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : '';

        $remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $remote_addr .= isset($_SERVER['REMOTE_PORT']) ? ':' . $_SERVER['REMOTE_PORT'] : '';

        $extra = array(
            'app_name' => \AgaviConfig::get('core.app_name'),
            'agavi_context' => $agavi_context->getName(),
            'agavi_environment' => \AgaviConfig::get('core.environment'),
            'agavi_version' => \AgaviConfig::get('agavi.version'),
            'php_version' => phpversion(),
            'system' => php_uname(),
            'pid' => getmypid(),
            'memory_usage' => self::formatBytes(memory_get_usage(true)),
            'memory_peak_usage' => self::formatBytes(memory_get_peak_usage(true))
        );

        if (!empty($_SERVER['SERVER_SOFTWARE']))
        {
            $extra['server_software'] = $_SERVER['SERVER_SOFTWARE'];
        }

        $extra['remote_addr'] = $remote_addr;

        if (!empty($_SERVER['X_FORWARDED_FOR']))
        {
            $extra['x_forwarded_for'] = $_SERVER['X_FORWARDED_FOR'];
        }

        if (!empty($server_name))
        {
            $extra['server_name'] = $server_name;
        }

        if (!empty($server_addr))
        {
            $extra['server_addr'] = $server_addr;
        }

        /*
         * the following is only accessible validated or while initialization
         */
        /*
        'http_host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
        'http_accept' => isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '',
        'http_accept_language' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '',
        'http_accept_encoding' => isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '',
        'http_cookie' => isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : '',
        'http_connection' => isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : '',
        'http_cache_control' => isset($_SERVER['HTTP_CACHE_CONTROL']) ? $_SERVER['HTTP_CACHE_CONTROL'] : '',
         */

        $extra['request_uri'] = $request_uri;
        $extra['request_method'] = $agavi_context->getRequest()->getMethod();
        $extra['matched_module_and_action'] = $matched_module_and_action;
        $extra['matched_routes'] = $matched_routes;
        $extra['first_container_output_type'] = $first_output_type;
        $extra['first_container_module_action_and_view'] = $first_module_action_and_view;

        $ssh_connection = getenv('SSH_CONNECTION');
        if (!empty($ssh_connection))
        {
            $extra['ssh_connection'] = $ssh_connection;
        }

        $user = getenv('USER');
        if (!empty($user))
        {
            $extra['CLI User'] = $user;
            $extra['CLI Home'] = getenv('HOME');
        }

        return $extra;
    }

    /**
     * Formats bytes into a human readable string.
     *
     * @param int $bytes
     *
     * @return string
     */
    protected static function formatBytes($bytes)
    {
        $bytes = (int) $bytes;

        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

        return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 3) . ' ' . $units[$i];
    }

    /**
     * @return bool true if argument is an associative array. False otherwise.
     */
    public static function isAssoc($a)
    {
        if (!is_array($a) || empty($a))
        {
            return false;
        }

        foreach (array_keys($a) as $k => $v)
        {
            if ($k !== $v)
            {
                return true;
            }
        }

        return false;
    }
}
