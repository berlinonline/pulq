<?php

namespace Pulq\Agavi\Validator;
use \AgaviValidator;
use \AgaviConfig;
use \AgaviDatabaseException;

class DatabaseNameValidator extends AgaviValidator
{
    protected function validate()
    {
        $db_name = $this->getData($this->getArgument());

        try {
            $db = $this->getContext()->getDatabaseManager()->getDatabase($db_name);
        } catch (AgaviDatabaseException $e) {
            $this->throwError();
            return false;
        }

        $this->export($db, $this->getArgument());
        return true;

    }
}
