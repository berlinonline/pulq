<?php

namespace Pulq\Agavi\Validator;

class ArrayValidator extends \AgaviValidator
{
    protected function validate()
    {
        $data = $this->getData($this->getArgument());
        // @todo add a more detailed (secure & configurable) implementation.
        if (is_array($data))
        {
            if ($this->hasParameter('export'))
            {
                $this->export($data, $this->getParameter('export'));
            }
            else
            {
                $this->export($data, $this->getArgument());
            }
            return TRUE;
        }

        $this->throwError('format');
        return FALSE;
    }  
}
