<?php

namespace Pulq\CodeGen\Agavi;

use \Pulq\CodeGen\TwigBuilder;

class ModuleBuilder extends TwigBuilder
{
    protected $module_dir;
    protected $module_name;

    const DIR_MODE = 0775;

    public function __construct($module_name)
    {
        $this->module_dir = realpath(AgaviConfig::get('core.app_dir') . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'project' . DIRECTORY_SEPARATOR .
            'modules');

        $this->module_name = $module_name;
        parent::__construct();
    }

    protected function getTemplateDirs()
    {
        return array(
            dirname(__FILE__).DIRECTORY_SEPARATOR.'templates',
        );
    }

    public function build()
    {
        $this->setupDirectories();
    }

    public function setupDirectories()
    {
        foreach($this->getDirectoryLayout() as $directory) {
            $dir_path = $this->module_dir .
                DIRECTORY_SEPARATOR .
                $this->module_name .
                DIRECTORY_SEPARATOR .
                $directory;
            $success = mkdir($dir_path, self::DIR_MODE, $recursive = true);
            if (!$success) {
                throw new Exception("Could not create directory $dir_path");
            }
        }
    }

    protected function getDirectoryLayout()
    {
        return array(
            'config',
            'impl',
            'lib',
            'lib/agavi',
            'lib/agavi/action',
            'lib/agavi/view',
        );
    }
}
