<?php

namespace Pulq\Environaut\Checks;

use Environaut\Checks\Check;

class SharedSecretCheck extends Check
{
    public function run()
    {
        $path = $this->parameters->get('path');
        $question = $this->parameters->get('confirm_question');
        $length = $this->parameters->get('length', 16);

        $dialog = $this->getDialogHelper();
        $output = $this->getOutputStream();

        if (file_exists($path)) {
            $question = "<question>$confirm_question</question> (Type [y/n/<return>], default=n): ";
            $value = $dialog->askConfirmation($output, $question, $default = false);
            if (!$value) {
                return true;
            }
        }

        file_put_contents($path, $this->getRandomString($length, ''));

        return true;
    }
}
