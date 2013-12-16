<?php

namespace Pulq\Agavi\Validator;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

class CliCheckBackValidator extends \AgaviValidator
{
    protected $passthrough_parameters;

	public function initialize(\AgaviContext $context, array $parameters = array(), array $arguments = array(), array $errors = array())
    {
        parent::initialize($context, $parameters, $arguments, $errors);

        $this->passthrough_parameters = $this->getParameters();

        $checkback_params = $this->getParameter('checkback', array());

        if (isset($checkback_params['question'])) {
            $this->setParameter('question', $checkback_params['question']);
        } else {
            throw new \AgaviValidatorException('The CliCheckBackValidator requires a "question" parameter.');
        }

        if (isset($checkback_params['validator'])) {
            $this->setParameter('validator', $checkback_params['validator']);
        } else {
            throw new \AgaviValidatorException('The CliCheckBackValidator requires a "validator" parameter.');
        }

        if (isset($checkback_params['attempts'])) {
            $this->setParameter('attempts', $checkback_params['attempts']);
        } else {
            $this->setParameter('attempts', 1);
        }
    }

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
            $this->passthrough_parameters,
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
            $value = $dialog->ask($output, '<question>'.$this->getParameter('question') . ' </question>', "");
            if ($this->checkValue($value)) {
                break;
            }
            $i++;
        }

        fclose($stdin);

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

