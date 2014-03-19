<?php

namespace Pulq\Agavi\Validator;
use \AgaviValidator;
use \AgaviConfig;
use \AgaviContext;
use \AgaviDatabaseException;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

class DatabaseNameValidator extends AgaviValidator
{
    protected function validate()
    {
        $db_name = $this->askForValue();

        $db = $this->getContext()->getDatabaseManager()->getDatabase($db_name);

        $this->export($db, $this->getArgument());
        return true;

    }

    protected function askForValue()
    {
        $options = $this->getContext()->getDatabaseManager()->getDatabaseNames();

        $stdin = fopen('/dev/tty', 'r');
        $output = new ConsoleOutput();

        $dialog  =new DialogHelper($output);
        $dialog->setInputStream($stdin);

        $value = $dialog->select($output, '<question>Select database</question>', $options, 0);

        fclose($stdin);

        return $options[$value];
    }

    protected function checkAllArgumentsSet($throwError = true)
    {
         /*
          * see AgaviValidator::checkAllArgumentsSet();
          * used to export default value in case of missing parameter
          *
          * basically this runs the validator even,
          * if the argument is not present
          * and the validator not required
          *
          */
        return true;
    }
}
