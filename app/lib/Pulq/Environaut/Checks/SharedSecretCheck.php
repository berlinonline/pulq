<?php

namespace Pulq\Environaut\Checks;

use Environaut\Checks\Check;
use Symfony\Component\Console\Output\ConsoleOutput;

class SharedSecretCheck extends Check
{
    public function run()
    {
        $path = $this->parameters->get('path');
        $question = $this->parameters->get('confirm_question');
        $length = $this->parameters->get('length', 16);

        $stdin = fopen('/dev/tty', 'r');
        $dialog = $this->getDialogHelper();
        $dialog->setInputStream($stdin);

        $output = $this->getOutputStream();

        if (file_exists($path)) {
            $question = "<question>$question</question> (Type [y/n/<return>], default=n): ";
            $value = $dialog->askConfirmation($output, $question, $default = false);
            if (!$value) {
                return true;
            }
        }

        file_put_contents($path, $this->getRandomString($length, ''));

        return true;
    }
}
