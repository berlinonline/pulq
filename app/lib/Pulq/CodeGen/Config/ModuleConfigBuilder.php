<?php

namespace Pulq\CodeGen\Config;

class ModuleConfigBuilder {

    protected $module_dir;
    protected $modules;

    public function __construct()
    {
        $template_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'templates';
        $loader = new \Twig_Loader_Filesystem(array(
            $template_dir,
            $template_dir . DIRECTORY_SEPARATOR . 'base'
        ));
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => \AgaviConfig::get('core.app_dir').'/cache/twig',
        ));
        $this->module_dir = \AgaviConfig::get('core.module_dir');
        $this->modules = $this->getModules();
    }

    public function build()
    {
        $this->buildFile('routing.xml', 'routing.include.xml.twig');
        $this->buildFile('autoload.xml', 'autoload.include.xml.twig');
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

        $template = $this->twig->loadTemplate($template_file);
        $routing = $template->render(array(
            'modules' => $modules
        ));

        $this->writeIncludeConfig($filename, $routing);
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
        file_put_contents($path, $content);
    }
}
