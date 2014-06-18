<?php
require(VENDOR_DIR."/autoload.php");
require(VENDOR_DIR."/agavi/agavi/src/agavi.php");

AgaviConfig::set('core.vendor_dir', VENDOR_DIR);
AgaviConfig::set('core.app_dir', BASE_DIR . "/app");
AgaviConfig::set('core.config_dir', BASE_DIR . "/app/config");
AgaviConfig::set('core.pub_dir', BASE_DIR . "/pub");
AgaviConfig::set('core.project_dir', BASE_DIR);
AgaviConfig::set('core.modules_dir', BASE_DIR . 'app/modules');
AgaviConfig::set('core.agavi_dir', VENDOR_DIR . "/agavi/agavi/src");
AgaviConfig::set('core.pulq_dir', VENDOR_DIR . "/berlinonline/pulq");

// without this, the template_dir setting in settings.xml won't work …
AgaviConfig::set('core.template_dir', null);

date_default_timezone_set('Europe/Berlin');
