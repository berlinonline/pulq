<?php
require(dirname(__DIR__) . '/vendor/autoload.php');
require(dirname(__DIR__) . '/vendor/agavi/agavi/src/agavi.php');

AgaviConfig::set('core.app_dir', __DIR__);
AgaviConfig::set('core.pub_dir', realpath(__DIR__.'/../pub'));
AgaviConfig::set('core.project_dir', realpath(__DIR__.'/../../project'));
AgaviConfig::set('core.modules_dir', __DIR__ . DIRECTORY_SEPARATOR . 'modules');
AgaviConfig::set(
    'core.agavi_dir', 
    dirname(__DIR__) . str_replace(
        '/', DIRECTORY_SEPARATOR, '/vendor/agavi/agavi/src/'
    )
);

// without this, the template_dir setting in settings.xml won't work …
AgaviConfig::set('core.template_dir', null);

date_default_timezone_set('Europe/Berlin');
