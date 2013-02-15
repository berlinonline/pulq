<?php

namespace Pulq\Agavi\Action;

/**
 * The BaseAction serves as the base action to all actions implemented inside of pulq.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class BaseAction extends \AgaviAction
{
    /**
     * Default error handling for method Read (GET Requests)
     *
     * @param \AgaviRequestDataHolder $parameters
     * @return array (modulename, viewname)
     */
    public function handleError(\AgaviRequestDataHolder $parameters)
    {
        $errors = array();

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[] = implode(', ', $errMsg['errors']) . ': ' . $errMsg['message'];
        }

        $this->setAttribute('errors', $errors);

        return 'Error';
    }

    public function isSecure()
    {
        return TRUE;
    }

    protected function logError($msg)
    {
        $logger = $this->getContext()->getLoggerManager()->getLogger('error');
        $errMsg = sprintf("[%s] %s", get_class($this), $msg);
        $logger->log(
            new \AgaviLoggerMessage($errMsg, \AgaviLogger::ERROR)
        );
    }

    protected function logInfo($msg)
    {
        $logger = $this->getContext()->getLoggerManager()->getLogger('app');
        $infoMsg = sprintf("[%s] %s", get_class($this), $msg);
        $logger->log(
            new \AgaviLoggerMessage($infoMsg, \AgaviLogger::INFO)
        );
    }

    /**
     * add a validation error out of the action
     *
     * @param string $argument argument name
     * @param string $message error message
     * @param int $severity
     * @return AgaviValidationIncident the generated error
     */
    protected function addError($argument, $message, $severity = \AgaviValidator::ERROR)
    {
        $validation_manager = $this->getContainer()->getValidationManager();
        $incident = new \AgaviValidationIncident(NULL, $severity);
        $incident->addError(new \AgaviValidationError($message, NULL, array($argument)));
        $validation_manager->addIncident($incident);

        return $incident;
    }
}
