<?php

class ProjectArrayValidator extends AgaviValidator
{
    protected function validate()
    {
        $data = $this->getData($this->getArgument());

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

?>
