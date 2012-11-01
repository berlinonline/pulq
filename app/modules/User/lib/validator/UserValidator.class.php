<?php

class UserValidator extends AgaviValidator
{
    protected function validate()
    {
        $value =& $this->getData($this->getArgument());

        // validate ...
        $this->export($value, $this->getArgument());
        return TRUE;
    }
}

?>
