<?php

use \AgaviConfig;

class Util_RequireJsAction extends UtilBaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $result = 0;
        passthru('node_modules/.bin/r.js -o pub/static/js/build.js', $result);
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
