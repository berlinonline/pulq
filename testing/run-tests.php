<?php

$testingEnabled = true;

require dirname(__DIR__) . '/app/dispatch.php';

AgaviToolkit::clearCache();

// Workaround to prevent session_start() warnings after previous output
session_start();

AgaviTesting::bootstrap(
    ProjectEnvironmentConfig::toEnvString()
);

PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(AgaviConfig::get('core.agavi_dir'));
PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(AgaviConfig::get('core.cache_dir'));

$output = array();
$setupFixturesCmd = dirname(__FILE__) . '/setup_fixtures.sh';
exec($setupFixturesCmd, $output);
//error_log(print_r($output, TRUE));

AgaviTesting::dispatch(
    AgaviTesting::processCommandlineOptions()
);

?>