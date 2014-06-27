<?php

namespace Pulq\CodeGen\Agavi;

use Pulq\CodeGen\TwigBuilder;
use \AgaviConfig;
use \Exception;

class ActionBuilder extends TwigBuilder
{
    protected $action_dir;
    protected $module_name;
    protected $action_name;

    const DIR_MODE = 0775;

    public function __construct($module_name, $action_name)
    {
        $this->action_dir = AgaviConfig::get('core.modules_dir') .
            '/' . $module_name .
            '/impl/' . $action_name;

        $this->module_name = $module_name;
        $this->action_name = $action_name;
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
        $this->setupDirectory();
        $this->buildFiles();
    }

    protected function setupDirectory()
    {
        $dir_path = $this->action_dir;
        if (!is_dir($dir_path)) {
            $success = mkdir($dir_path, self::DIR_MODE, $recursive = true);
            if (!$success) {
                throw new Exception("Could not create directory $dir_path");
            }
        }
    }

    protected function buildFiles()
    {
        $config_files = array(
            'validate.xml',
            'Action.class.php',
            'SuccessView.class.php',
            'ErrorView.class.php',
            'Success.twig',
            'Error.twig'
        );

        $template_dir = 'action/';

        foreach($config_files as $filename) {
            $content = $this->renderTemplate($template_dir.$filename.'.twig', array(
                'module_name' => $this->module_name,
                'action_name' => $this->action_name
            ));

            if ($filename == 'validate.xml') {
                $filename = '.validate.xml';
            }

            $result = file_put_contents($this->action_dir.'/'.$this->action_name.$filename, $content);
            if ($result === false) {
                throw new \Exception($target_dir.$filename." could not be written");
            }
        }
    }
}

