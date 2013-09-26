<?php

use \AgaviConfig;

class Util_ScssAction extends UtilBaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        if ( AgaviConfig::get('minify.css', false) ) {
            $scss_style = 'compressed';
        } else {
            $scss_style = 'nested';
        }

        $result = 0;
        passthru('scss --style '.$scss_style.' pub/static/scss/main.scss pub/static/style.css', $result);
        if ($result === 0) {
            return 'Success';
        } else {
            return 'Error';
        }
    }

    public function isSecure()
    {
        return FALSE;
    }
}
