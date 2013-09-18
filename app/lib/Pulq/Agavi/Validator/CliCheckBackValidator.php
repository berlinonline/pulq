<?php

namespace Pulq\Agavi\Validator;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

class CliCheckBackValidator extends \AgaviValidator
{
    public function validate()
    {
        $argument = $this->getArgument();
        $data = $this->getData($argument);

        $validator_class = $this->getParameter('validator', "\AgaviIssetValidator");

        $this->validationParameters->setParameter($argument, $data);

        $validator = new $validator_class();
        $validator->setParentContainer($this->getParentContainer());
        $validator->initialize(
            $this->getContext(),
            $this->getParameters(),
            $this->getArguments(),
            $this->errorMessages
        );

        $valid =  $validator->execute($this->validationParameters);

        return $valid === 0 ? true : false;
    }

    protected function &getData($paramName)
    {
        $data = parent::getData($paramName);

        if (!$data) {
            $data = $this->askForValue();
        }

        return $data;
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

    protected function askForValue()
    {
        $attempts = $this->getParameter('attempts', 1);
        $i = 0;

        $stdin = fopen('/dev/tty', 'r');
        $output = new ConsoleOutput();

        $dialog  =new DialogHelper($output);
        $dialog->setInputStream($stdin);

        while($i < $attempts) {
            $value = $dialog->ask($output, "O rly??", "Ya rly!!");
            if ($this->checkValue($value)) {
                break;
            }
        }

        return $value;
    }

    protected function checkValue($value)
    {
        if (trim((string)$value)) {
            return true;
        } else {
            return false;
        }
    }
}

