<?php

namespace Pulq\Agavi\Request;

use \AgaviWebRequest;
use \AgaviContext;

class PulqWebRequest extends AgaviWebRequest
{
    protected $https_headers = array(
        'HTTPS' => 'yes',
        'X_SSL' => 'yes',
    );

    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);

        $force_https = false;

        foreach ($this->https_headers as $header_name => $header_value) {
            $full_header_name = "HTTP_$header_name";

            if (isset($_SERVER[$full_header_name])) {
                if ($_SERVER[$full_header_name] === $header_value) {
                    $force_https = true;
                }
            }
        }

        if ($force_https) {
            $this->urlPort = 443;
            $this->urlScheme = "https";
        }
    }

    public function getUrl($scheme = null)
    {
        $url_scheme = $scheme ? $scheme : $this->getUrlScheme();

        return
            $url_scheme . '://' .
            $this->getUrlAuthority() .
            $this->getRequestUri();
    }
}
