<?php

use Pulq\CodeGen\Config\ModuleConfigBuilder;
use \AgaviConfig;

class Util_LinkModulesAction extends UtilBaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $project_module_dir = AgaviConfig::get('core.app_dir').'/../../project/modules';
        $module_dir = AgaviConfig::get('core.module_dir');

        # clear the existing symlinks from the Agavi module dir
        foreach(scandir($module_dir) as $filename) {
            $path = $module_dir.'/'.$filename;
            if (is_link($path)) {
                unlink($path);
            }
        }

        #make new links for the project modules
        foreach(scandir($project_module_dir) as $filename) {
            if ($filename === ".." || $filename === ".") {
                continue;
            }
            $source_path = $project_module_dir.'/'.$filename;
            $target_path = $module_dir.'/'.$filename;
            if (is_dir($source_path)) {
                symlink($source_path, $target_path);
            }
        }

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
