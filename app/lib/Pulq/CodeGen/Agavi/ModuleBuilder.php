<?php

namespace Pulq\CodeGen\Agavi;

use Pulq\CodeGen\TwigBuilder;
use \AgaviConfig;
use \Exception;

class ModuleBuilder extends TwigBuilder
{
    protected $module_dir;
    protected $module_name;

    const DIR_MODE = 0775;

    public function __construct($module_name)
    {
        $this->module_dir = AgaviConfig::get('core.modules_dir') .
            '/' . $module_name;

        $this->module_name = $module_name;
        parent::__construct();
    }

    protected function getTemplateDirs()
    {
        return array(
            dirname(__FILE__).'/templates',
        );
    }

    public function build()
    {
        $this->setupDirectories();
        $this->buildConfigs();
        $this->buildBaseAction();
        $this->buildBaseView();
    }

    public function setupDirectories()
    {
        foreach($this->getDirectoryLayout() as $directory) {
            $dir_path = $this->module_dir.'/'.$directory;
            if (!is_dir($dir_path)) {
                $success = mkdir($dir_path, self::DIR_MODE, $recursive = true);
                if (!$success) {
                    throw new Exception("Could not create directory $dir_path");
                }
            }
        }
    }

    protected function getDirectoryLayout()
    {
        return array(
            'config',
            'impl',
            'lib',
            'lib/Project',
            'lib/Project/'.$this->module_name.'/Agavi',
            'lib/Project/'.$this->module_name.'/Agavi/Action',
            'lib/Project/'.$this->module_name.'/Agavi/View',
        );
    }

    protected function buildConfigs()
    {
        $config_files = array(
            'settings.xml',
            'routing.xml',
            'module.xml',
            'autoload.xml',
            'namespaces.xml',
            'translation.xml'
        );

        $config_template_dir = 'module/config/';
        $target_dir = $this->module_dir . '/config/';

        foreach($config_files as $filename) {
            $content = $this->renderTemplate($config_template_dir.$filename.'.twig', array(
                'module_name' => $this->module_name
            ));

            $result = file_put_contents($target_dir.$filename, $content);
            if ($result === false) {
                throw new \Exception($target_dir.$filename." could not be written");
            }
        }
    }

    protected function buildBaseView()
    {
        $content = $this->renderTemplate(
            'module/lib/Agavi/View/BaseView.php.twig',
            array (
                'module_name' => $this->module_name
            )
        );

        $filename = $this->module_dir.'/lib/Project/'.$this->module_name.'/Agavi/View/BaseView.php';

        $result = file_put_contents($filename, $content);

        if ($result === false) {
            throw new \Exception($filename." could not be written");
        }
    }

    protected function buildBaseAction()
    {
        $content = $this->renderTemplate(
            'module/lib/Agavi/Action/BaseAction.php.twig',
            array (
                'module_name' => $this->module_name
            )
        );

        $filename = $this->module_dir.'/lib/Project/'.$this->module_name.'/Agavi/Action/BaseAction.php';

        $result = file_put_contents($filename, $content);

        if ($result === false) {
            throw new \Exception($filename." could not be written");
        }
    }
}
