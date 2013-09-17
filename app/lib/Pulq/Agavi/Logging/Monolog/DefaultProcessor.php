<?php

namespace Pulq\Agavi\Logging\Monolog;

use Pulq\Agavi\Logging\LoggerManager;

/**
 * Default processor for Monolog log messages that adds Agavi, system and
 * application specific information to the extra field of the log record.
 */
class DefaultProcessor
{
    /**
     * @param array $record Monolog log record with message, context, extra
     *
     * @return array with additional extra information
     */
    public function __invoke(array $record)
    {
        $record['extra'] = array_merge(
            $record['extra'],
            LoggerManager::getExtraInformation()
        );

        return $record;
    }
}
