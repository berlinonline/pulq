<?php

namespace Pulq\Agavi\Logging;

use \Psr\Log\LogLevel;
use Pulq\Agavi\Logging\FileLoggerAppender;

/**
 * Extends \AgaviLogger with some convenience methods and the possibility to
 * get a PSR-3 compatible logger. This logger listens to ALL log levels by
 * default and selectively writes messages to all logger appenders that are
 * appropriate according to their max_level parameter.
 */
class Logger extends \AgaviLogger
{
    /**
     * Default log level for this logger is to accept ALL log messages.
     */
    protected $level = \AgaviLogger::ALL;

    /**
     * @var array associative array of supported Agavi log levels.
     */
    protected static $levels = array(
        256 => 'TRACE',
        128 => 'DEBUG',
         64 => 'INFO',
         32 => 'NOTICE',
         16 => 'WARNING',
          8 => 'ERROR',
          4 => 'CRITICAL',
          2 => 'ALERT',
          1 => 'EMERGENCY'
    );

    /**
     * @var array mapping from PSR-3 defined log levels to Agavi log levels.
     */
    protected static $psr3_to_agavi_level_mapping = array(
        // LogLevel::TRACE => \AgaviLogger::TRACE,
        LogLevel::DEBUG     => \AgaviLogger::DEBUG,
        LogLevel::INFO      => \AgaviLogger::INFO,
        LogLevel::NOTICE    => \AgaviLogger::NOTICE,
        LogLevel::WARNING   => \AgaviLogger::WARNING,
        LogLevel::ERROR     => \AgaviLogger::ERROR,
        LogLevel::CRITICAL  => \AgaviLogger::CRITICAL,
        LogLevel::ALERT     => \AgaviLogger::ALERT,
        LogLevel::EMERGENCY => \AgaviLogger::EMERGENCY
    );

    /**
     * Gets all supported logging levels.
     *
     * @return array associative array with human-readable Agavi log level names => log level codes.
     */
    public static function getLevels()
    {
        return array_flip(static::$levels);
    }

    /**
     * Returns the name of the given Agavi log level.
     *
     * @param mixed $level Agavi log level severity or instance of \AgaviLoggerMessage
     *
     * @return string name of given severity value
     */
    public static function getLevelName($level)
    {
        if ($level instanceof \AgaviLoggerMessage)
        {
            $level = $level->getLevel();
        }

        if (!isset(static::$levels[$level]))
        {
            throw new \InvalidArgumentException("Log level '$level' is undefined, use one of: " . implode(', ', array_keys(static::$levels)));
        }

        return static::$levels[$level];
    }

    /**
     * @return \Pulq\Agavi\Logging\Psr3Logger instance that is compatible to the PSR-3 standard
     */
    public function getPsr3Logger()
    {
        return new Psr3Logger($this);
    }

    /**
     * @param string $psr3_log_level PSR-3 log level like \Psr\Log\LogLevel::DEBUG
     * @param int $default default Agavi log level to use when mapping fails or level is unknown
     *
     * @return int Agavi log level or given default
     */
    public static function getAgaviLogLevel($psr3_log_level, $default = \AgaviLogger::INFO)
    {
        if (is_string($psr3_log_level) && isset(self::$psr3_to_agavi_level_mapping[$psr3_log_level]))
        {
            return self::$psr3_to_agavi_level_mapping[$psr3_log_level]; // map PSR-3 log level to Agavi log level
        }
        elseif (isset(self::$levels[$psr3_log_level]))
        {
            return $psr3_log_level; // seems to be an Agavi log level so we use that
        }
        else
        {
            return $default; // default level as it's no known Agavi log level
        }
    }

    /**
     * @param string $agavi_log_level Agavi log level like \AgaviLogger::DEBUG
     * @param int $default default Pssr\Log\LogLevel to use when mapping fails or level is unknown
     *
     * @return string PSR-3 log level or given default
     */
    public static function getPsr3LogLevel($agavi_log_level, $default = Logger::INFO)
    {
        $map = array_flip(self::$psr3_to_agavi_level_mapping);
        if (is_int($agavi_log_level) && isset($map[$agavi_log_level]))
        {
            return $map[$agavi_log_level]; // map Agavi log level to PSR-3 log level
        }
        elseif (array_key_exists($agavi_log_level, self::$psr3_to_agavi_level_mapping))
        {
            return $agavi_log_level; // seems to be a PSR-3 log level so we use that
        }
        elseif ($agavi_log_level === \AgaviLogger::TRACE)
        {
            return Logger::DEBUG; // TRACE is not known in PSR-3, so we map it to DEBUG
        }
        else
        {
            return $default; // default level as it's no known Agavi log level
        }
    }

    /**
     * Log the given message to all appropriate appenders. Overrides the Agavi
     * default functionality by introducing a max_level parameter on the
     * appenders to specify the maximum log level an appender wants to be
     * notified for. This eases the introduction of appenders a bit as one
     * does not have to use the bit mask exclusively while introducing a slight
     * conlict potential for the parameter name 'max_level' of newly created
     * logger appenders.
     *
     * @param \AgaviLoggerMessage $message message instance to use for logging.
     *
     * @return void
     */
    public function log(\AgaviLoggerMessage $message)
    {
        $msg_level = $message->getLevel();

        if ($this->level & $msg_level)
        {
            foreach($this->appenders as $appender)
            {
                $max_appender_level = $appender->getParameter('max_level', null);
                $max_appender_level = ($max_appender_level !== NULL) ? constant($max_appender_level) : $this->level;
                if ($max_appender_level >= $msg_level)
                {
                    $appender->write($message);
                }
            }
        }
    }

    /**
     * Overrides default functionality with convenience to use the appender
     * name as the default destination (filename) for the FileLoggerAppender.
     * This makes those appenders easier to use as their name is the log file
     * name by default.
     *
     * @param string $name name to use for given logger appender
     * @param \AgaviLoggerAppender $appender instance of append to use
     *
     * @return void
     *
     * @throws \AgaviLoggingException if there is already an appender registered for the given name
     */
    public function setAppender($name, \AgaviLoggerAppender $appender)
    {
        if (!isset($this->appenders[$name]))
        {
            if ($appender->getParameter('destination', null) === FileLoggerAppender::USE_APPENDER_NAME_AS_DESTINATION)
            {
                $name = preg_replace('/[^a-zA-Z0-9-_\.\/]/', '', $name); // sanitize logger name to very basic name
                $appender->setParameter('destination', \AgaviConfig::get('core.app_dir') . '/log/' . $name . '.log');
            }

            $this->appenders[$name] = $appender;

            return;
        }

        throw new \AgaviLoggingException(sprintf('An appender with the name "%s" is already registered.', $name));
    }
}
