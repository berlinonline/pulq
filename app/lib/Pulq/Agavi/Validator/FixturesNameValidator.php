<?php

namespace Pulq\Agavi\Validator;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

class FixturesNameValidator extends \AgaviValidator
{
    public function validate()
    {
        $value = $this->askForValue();

        $this->export($value, $this->getArgument());

        return true;
    }

    protected function askForValue()
    {
        $options = $this->getFixtureSets();

        $stdin = fopen('/dev/tty', 'r');
        $output = new ConsoleOutput();

        $dialog  =new DialogHelper($output);
        $dialog->setInputStream($stdin);

        $value = $dialog->select($output, '<question>Select fixture set</question>', $options, 0);

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

    protected function getFixtureSets()
    {
        $fixtures_dir = \AgaviConfig::get('core.fixtures_dir');

        $dirs = glob($fixtures_dir.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);

        if (empty($dirs)) {
            throw new \Exception('No fixture sets found!');
        }

        $names = array_map(function($d) {
            return basename($d);
        }, $dirs);

        return $names;
    }
}

