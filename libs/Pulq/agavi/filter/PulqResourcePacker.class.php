<?php

/**
 * The PulqScriptPacker packs an compresses js and css scripts.
 *
 * @version         $Id: PulqScriptPacker.class.php 1063 2012-04-05 11:59:16Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Pulq
 * @subpackage      Deployment
 */
class PulqResourcePacker
{
    protected $config;

    protected $modules;

    protected static $types = array(
        'scripts' => array(
            'extension'=> 'js'
        ),
        'styles' => array(
            'extension' => 'css'
        )
    );

    public function __construct(array $modules, $outputType, PulqResourceFilterConfig $config)
    {
        $this->config = $config;
        $this->modules = $modules;
        $this->outputType = $outputType;
    }

    public function pack()
    {
        foreach($this->modules[$this->outputType] as $module)
        {
            $sourceDir = $this->getResourceDirectoryForModule($module);
            $targetDir = $this->config->getCacheDir() . DIRECTORY_SEPARATOR . $module;
            
            $this->moveResources($sourceDir, $targetDir);
        }

        $this->moveResources(
            AgaviConfig::get('core.app_dir').DIRECTORY_SEPARATOR.'resources',
            $this->config->getCacheDir().DIRECTORY_SEPARATOR.'_global'
        );
    }

    protected function moveResources($sourceDir, $targetDir)
    {
        $directories = array('scripts', 'styles', 'binaries');
        foreach ($directories as $directory)
        {
            if (!is_dir($sourceDir))
            {
                throw new Exception($sourceDir . ' is not a directory. Please move the file into on of the resource subdirectories.');
            }

            if (!$this->config->isPackingEnabled())
            {
                $this->copyResources($sourceDir, $targetDir);
                continue;
            }

            $sDir = $sourceDir.DIRECTORY_SEPARATOR.$directory;
            $tDir = $targetDir.DIRECTORY_SEPARATOR.$directory;
            switch ($directory)
            {
                case 'scripts':
                    $this->packScripts($sDir, $tDir);
                    break;
                case 'styles':
                    $this->packStyles($sDir, $tDir);
                    break;
                default:
                    $this->copyResources($sDir, $tDir);
            }
        }
    }

    protected function getFileExtension($type)
    {
        if (!array_key_exists($type, static::$types))
        {
            throw new Exception('Unknown MIME type: ' . $type);
        }

        return static::$types[$type]['extension'];
    }

    protected function getResourceDirectoryForModule($module)
    {
        $resourcesDir = AgaviConfig::get('core.modules_dir')
            . DIRECTORY_SEPARATOR
            . $module
            . DIRECTORY_SEPARATOR
            . 'resources';

        return $resourcesDir;
    }

    protected function checkForOutdatedPacking($sourceFiles, $targetFile)
    {
        if(file_exists($targetFile))
        {
            $targetMTime = filemtime($targetFile);

            foreach($sourceFiles as $sourceFile)
            {
                if (filemtime($sourceFile) > $targetMTime)
                {
                    return true; //refreshed file found!
                }
            }

            return false; //source files are not newer than target
        }

        return true; //target doesn't exist so it's considered outdated
    }

    protected function packScripts($from, $to)
    {
        $uglifyPath = str_replace('/', DIRECTORY_SEPARATOR, AgaviConfig::get('core.app_dir').'/../libs/node_modules/uglifyjs/bin/uglifyjs');
        $scriptFiles = glob($from.DIRECTORY_SEPARATOR.'*');
        $outputPath = $to . DIRECTORY_SEPARATOR . 'combined.js';
        
        if (!$this->checkForOutdatedPacking($scriptFiles, $outputPath))
        {
            return;
        }

        $scripts = array();

        foreach ($scriptFiles as $file)
        {
            $scripts[$file] = shell_exec($uglifyPath.' '.$file);
        }

        $this->ensureDirectoryExists($to);
        file_put_contents($outputPath, $this->concatParts($scripts));
    }

    protected function packStyles($from, $to)
    {
        $lesscPath = str_replace('/', DIRECTORY_SEPARATOR, AgaviConfig::get('core.app_dir').'/../libs/node_modules/less/bin/lessc');
        $styleFiles = glob($from.DIRECTORY_SEPARATOR.'*');
        $outputPath = $to . DIRECTORY_SEPARATOR . 'combined.css';
        
        if (!$this->checkForOutdatedPacking($styleFiles, $outputPath))
        {
            return;
        }

        $styles = array();

        foreach ($styleFiles as $file)
        {
            if (preg_match('#.less$#', $file))
            {
                if (preg_match('#\.import\.less$#', $file))
                {
                    continue;
                }
                $fileContents = shell_exec($lesscPath.' '.$file);
            }
            else
            {
                $fileContents = file_get_contents($file);
            }
            $styles[$file] = $fileContents;
        }

        $this->ensureDirectoryExists($to);
        file_put_contents($outputPath, $this->concatParts($styles));
    }

    protected function copyResources($from, $to)
    {
        $this->ensureDirectoryExists($to);
        $files = glob($from.DIRECTORY_SEPARATOR.'*');
        foreach($files as $file)
        {
            $this->recursiveCopy($file, $to.DIRECTORY_SEPARATOR.basename($file));
        }
    }

    protected function recursiveCopy($from, $to)
    {
        if(!is_dir($from) )
        {
            if (file_exists($to) && filemtime($from) > filemtime($to))
            {
                return true;
            }

            return copy($from, $to);
        }

        $dir = opendir($from);
        $success = true;

        while($success && $currentFile = readdir($dir))
        {
            if (preg_match("#^\.#", $currentFile))
            {
                continue;
            }

            $success = $this->recursiveCopy($from.DIRECTORY_SEPARATOR.$currentFile, $to.DIRECTORY_SEPARATOR.$currentFile);
        }

        return $success;
        
    }

    protected function concatParts(array $parts)
    {
        $concat = '';

        foreach($parts as $name => $part)
        {
            $concat .= "\n\n/* $name */\n\n" . $part;
        }

        return $concat;
    }

    protected function ensureDirectoryExists($path)
    {
        $success = true;
        if (!is_dir($path))
        {
            $success = mkdir($path, 0777, true);
        }
 
        clearstatcache(true, $path);
        
        return $success;
    }
}