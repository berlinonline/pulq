<?php

namespace Pulq\Consumer\Services;

class SignatureService
{
    protected $shared_secret;

    const HASH_ALGORITHM = 'sha256';

    public function __construct()
    {
        $shared_secret_path = \AgaviConfig::get('consumer.shared_secret_path');

        $this->shared_secret = file_get_contents($shared_secret_path);
    }

    public function sign($message)
    {
        return hash_hmac(self::HASH_ALGORITHM, $message, $this->shared_secret);
    }
}
