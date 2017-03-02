<?php

namespace Pulq\Consumer\Agavi\Validate;

use Pulq\Consumer\Services\SignatureService;

class DocumentValidator extends \AgaviValidator
{
    protected function validate()
    {
        $data = $this->getData($this->getArgument());
        $put_filename = $data['tmp_name'];
        $put_data = file_get_contents($put_filename);

        $data = json_decode($put_data, true);

        if($data !== null ) {
            $this->export($data, $this->getArgument());
            return true;
        } else {
            $this->throwError('not_signed');
            return false;
        }
    }
}
