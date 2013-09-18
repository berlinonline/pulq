<?php

namespace Pulq\Agavi\Validator;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

class CliModuleNameValidator extends \AgaviValidator
{
    public function validate()
    {
        //plain
        /*
        echo __METHOD__.PHP_EOL;
        $stdin = fopen('php://stdin', 'rb');
        $foo = fgets($stdin, 1024);
        var_dump($foo);die();
        */

        //with symfony console
        $stdin = fopen('/dev/tty', 'r');
        $output = new ConsoleOutput();

        $dialog  =new DialogHelper($output);
        $dialog->setInputStream($stdin);
        $value = $dialog->ask($output, "O rly??", "Ya rly!!");
        var_dump($value);die();

        return true;
    }

    protected function checkAllArgumentsSet($throwError = true)
    {
         /*
          * see AgaviValidator::checkAllArgumentsSet();
          * used to export default value in case of missing parameter
          */
        return true;
    }

}
