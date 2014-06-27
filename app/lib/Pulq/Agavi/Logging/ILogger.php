<?php

namespace Pulq\Agavi\Logging;

/**
 * See RFC 5424 (Syslog) for log level severity names.
 */
interface ILogger
{
    /**
     * Verbose debugging. Syslog severity not defined.
     */
    public function logTrace();

    /**
     * Debug level messages. RFC 5424 syslog severity numerical code 7.
     */
    public function logDebug();

    /**
     * Informational messages. RFC 5424 syslog severity numerical code 6.
     */
    public function logInfo();

    /**
     * Normal but significant condition. RFC 5424 syslog severity numerical code 5.
     */
    public function logNotice();

    /**
     * Warning conditions. RFC 5424 syslog severity numerical code 4.
     */
    public function logWarning();

    /**
     * Error conditions. RFC 5424 syslog severity numerical code 3.
     */
    public function logError();

    /**
     * Critical conditions. RFC 5424 syslog severity numerical code 2.
     */
    public function logCritical();

    /**
     * Action must be taken immediately. RFC 5424 syslog severity numerical code 1.
     */
    public function logAlert();

    /**
     * System is unusable. RFC 5424 syslog severity numerical code 0.
     */
    public function logEmergency();
}
