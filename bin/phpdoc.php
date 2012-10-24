<?php 

$basePath = dirname(__FILE__).'/../libs/phpDocumentor2';

set_include_path($basePath.'/src'.PATH_SEPARATOR.get_include_path());

require $basePath.'/bin/phpdoc.php';
