<?php

namespace Pulq\Consumer\Agavi\Validate;

use \AgaviValidator;
use Pulq\Consumer\Services\SignatureService;

class SignedRequestValidator extends AgaviValidator
{
    protected function validate()
    {
        $id = $this->getData($this->getArgument());

        $rd = $this->getContext()->getRequest()->getRequestData();
        $signature = $rd->get('headers', 'SIGNATURE', '');

        $signature_service = new SignatureService();

        var_dump($signature_service->sign($id));

        if ($signature_service->sign($id) === $signature) {
            $this->export($id, $this->getArgument());
            return true;
        } else {
            $this->throwError('not_signed');
            return false;
        }
    }
}
