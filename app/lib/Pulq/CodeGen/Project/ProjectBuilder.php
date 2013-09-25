<?php

namespace Pulq\CodeGen\Project;

use Pulq\CodeGen\TwigBuilder;
use \AgaviConfig;
use \Exception;

class ProjectBuilder extends TwigBuilder
{
    protected $project_dir;
    protected $template_dir;

    const DIR_MODE = 0775;

    public function __construct()
    {
        $this->project_dir = AgaviConfig::get('core.app_dir') .
            '/../../project';
        $this->template_dir = dirname(__FILE__).'/templates';

        parent::__construct();
    }

    protected function getTemplateDirs()
    {
        return array(
            $this->template_dir,
        );
    }

    public function build()
    {
        $this->setupDirectories();
        $this->copyFiles();
        $this->buildFiles();
    }

    public function setupDirectories()
    {
        foreach($this->getDirectoryLayout() as $directory) {
            $dir_path = $this->project_dir.'/'.$directory;
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
            'app',
            'app/config',
            'app/templates',
            'app/templates/macros',
            'modules',
            'pub',
            'pub/static',
            'pub/static/js',
            'pub/static/scss',
            'pub/static/bin'
        );
    }

    protected function copyFiles()
    {
        $files = array(
            'app/config/settings.xml',
            'app/config/routing.xml',
            'app/config/output_types.xml',
            'app/templates/Master.twig',
            'pub/static/require.js',
            'pub/static/js/main.js',
            'pub/static/js/libs/jquery.min.js',
            'pub/static/js/libs/JsBehaviourToolkit.js',
            'pub/static/scss/_vars.scss',
            'pub/static/scss/main.scss',
        );

        $target_dir = $this->project_dir.'/';

        foreach($files as $filename) {
            $result = copy($this->template_dir.'/'.$filename, $target_dir.$filename);
            if ($result === false) {
                throw new \Exception($target_dir.$filename." could not be written");
            }
        }
    }

    protected function buildFiles()
    {
        $files = array(
        );

        $target_dir = $this->project_dir.'/';

        foreach($files as $filename) {
            $content = $this->renderTemplate($filename.'.twig', array(
            ));

            $result = file_put_contents($target_dir.$filename, $content);
            if ($result === false) {
                throw new \Exception($target_dir.$filename." could not be written");
            }
        }
    }
}
