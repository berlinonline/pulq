<?php

use Pulq\Util\Agavi\Action\BaseAction;
use Pulq\CodeGen\Config\ModuleConfigBuilder;
use \AgaviConfig;

class Util_BuildLinksAction extends BaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $this->linkModules();
        $this->linkPub();
        $this->linkConfig();

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }

    protected function linkModules()
    {
        $project_module_dir = AgaviConfig::get('core.app_dir').'/../../project/modules';
        $module_dir = AgaviConfig::get('core.module_dir');

        if (is_dir($project_module_dir)) {
            $this->linkDirContents($project_module_dir, $module_dir);
        }
    }

    protected function linkPub()
    {
        $project_pub_dir = AgaviConfig::get('core.app_dir').'/../../project/pub';
        $pub_dir = AgaviConfig::get('core.app_dir').'/../pub';

        if (is_dir($project_pub_dir)) {
            $this->linkDirContents($project_pub_dir, $pub_dir);
        }
    }

    protected function linkConfig()
    {
        $project_config_dir = AgaviConfig::get('core.app_dir').'/../../project/app/config';
        $config_dir = AgaviConfig::get('core.app_dir').'/project/config';

        if (is_dir($project_config_dir)) {
            $this->linkDirContents($project_config_dir, $config_dir);
        }
    }

    protected function linkDirContents($source, $target)
    {
        $source_path = realpath($source);

        if (!$source_path) {
            throw new Exception("Directory $source does not exist");
        }

        $target_path = realpath($target);

        if (!$target_path) {
            throw new Exception("Directory $target does not exist");
        }



        # clear the existing symlinks from the Agavi module dir
        foreach(scandir($target_path) as $filename) {
            $path = $target_path.'/'.$filename;
            if (is_link($path)) {
                unlink($path);
            }
        }

        #make new links for the project modules
        foreach(scandir($source_path) as $filename) {
            if ($filename === ".." || $filename === ".") {
                continue;
            }
            $link_source_path = realpath($source_path.'/'.$filename);
            $link_target_path = $target_path.'/'.$filename;
            symlink($link_source_path, $link_target_path);
            echo "linked $link_source_path to $link_target_path\n";
        }
    }
}
