<?php

namespace Pulq\Consumer\Agavi\Validate;

use \AgaviValidator;
use Pulq\Consumer\Services\SignatureService;

class SignedRequestValidator extends AgaviValidator
{
    protected function validate()
    {
        $id = $this->getData($this->getArgument());

        if (!preg_match('/[A-z0-9]+-[0-9]+/', $id)) {
            $this->throwError('id_malformed');
            return false;
        }

        $rd = $this->getContext()->getRequest()->getRequestData();
        $signature = $rd->get('headers', 'SIGNATURE', '');

        $signature_service = new SignatureService();

        if ($signature_service->sign($id) === $signature) {
            $this->export($id, $this->getArgument());
            return true;
        } else {
            $this->throwError('not_signed');
            return false;
        }
    }
}
