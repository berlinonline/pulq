<?php

namespace Pulq\CodeGen\Config;

use Pulq\CodeGen\TwigBuilder;

class ModuleConfigBuilder extends TwigBuilder
{

    protected $module_dir;
    protected $modules;
    protected $template_dir;

    public function __construct()
    {
        $this->template_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'templates';
        $this->module_dir = \AgaviConfig::get('core.module_dir');
        $this->modules = $this->getModules();
        parent::__construct();
    }

    public function build()
    {
        $this->buildFile('routing.xml', 'routing.include.xml.twig');
        $this->buildFile('autoload.xml', 'autoload.include.xml.twig');
        $this->buildFile('namespaces.xml', 'namespaces.include.xml.twig');
        $this->buildFile('settings.xml', 'settings.include.xml.twig');
        $this->buildFile('translation.xml', 'translation.include.xml.twig');
    }

    protected function buildFile($filename, $template_file)
    {
        $modules = array();

        foreach ($this->modules as $module) {
            if ($path = $this->getModuleConfigFilePath($module, $filename)) {
                $modules[] = $module;
            }
        }

        $content = $this->renderTemplate($template_file, array(
            'modules' => $modules
        ));

        $this->writeIncludeConfig($filename, $content);
    }

    protected function getModuleConfigFilePath($module, $filename) {
        $path = $this->module_dir . DIRECTORY_SEPARATOR .
            $module . DIRECTORY_SEPARATOR .
            'config' . DIRECTORY_SEPARATOR .
            $filename;

        if (is_file($path)) {
            return $path;
        } else {
            return false;
        }
    }

    protected function getModules()
    {
        return array_filter(scandir($this->module_dir), function($item) {
            if ($item === '..' || $item === '.') {
                return false;
            } else {
                return true;
            }
        });
    }

    protected function writeIncludeConfig($filename, $content)
    {
        $path = \AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR .
            'includes' . DIRECTORY_SEPARATOR .
            $filename;
        $result = file_put_contents($path, $content);

        if ($result === false) {
            throw new \Exception("$filename could not be written");
        }
    }

    protected function getTemplateDirs()
    {
        return array(
            $this->template_dir,
            $this->template_dir . DIRECTORY_SEPARATOR . 'base'
        );
    }
}
