<?php

/**
 * The HeroResourceFilter is responseable for detecting required scripts and deploying them for your view.
 *
 * @version         $Id: HeroResourceFilter.class.php 1065 2012-04-05 12:05:39Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Hero
 * @subpackage      Agavi/Filter
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class HeroResourceFilter extends AgaviFilter implements AgaviIGlobalFilter
{
    public static function addModule($moduleName, $outputType)
    {
        static::$modules[$outputType][] = $moduleName;
    }

    /**
     * Holds a string that respresents the utf8 encoding.
     */
    const ENCODING_UTF_8 = 'utf-8';

    /**
     * Holds a string that respresents the iso 8859-1 encoding.
     */
    const ENCODING_ISO_8859_1 = 'iso-8859-1';

    /**
     * Hold the list of modules that have been used in the current request.
     * Grouped by output_type.
     */

    protected static $modules = array();

    /**
     * Holds our config object.
     *
     * @var HeroResourceFilterConfig
     */
    protected $config;

    /**
     * Initialize the model, hence setup our config.
     *
     * @param AgaviContext $context
     * @param array $parameters
     */
    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);

        $this->config = new HeroResourceFilterConfig($parameters);
    }

    /**
	 * Add the scripts for all executed html views.
	 *
	 * @param AgaviFilterChain A FilterChain instance.
	 * @param AgaviExecutionContainer The current execution container.
	 */
    public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
    {
        $filterChain->execute($container);
        $response = $container->getResponse();
        $output = NULL;
        if (! $response->isContentMutable() || ! ($output = $response->getContent()))
        {
            // throw exception? we cant really live without our scripts...
            return FALSE;
        }

        $this->curOutputType = $response->getOutputType()->getName();
        if (!$this->config->isOutputTypeSupported($this->curOutputType))
        {
            // ot not supported, log to info or debug?
            return FALSE;
        }

        $packer = new HeroResourcePacker(self::$modules, $this->curOutputType, $this->config);
        $packer->pack();

        die();

        $scripts = array();
        if ($config->isPackingEnabled())
        {
            $scripts = array();
            foreach($modules as $module)
            {
                $scripts[] = $module . '.js';
            }

            $scripts[] = 'global.js';
        }
        else
        {
            $scripts = $packer->getCombinedFileName($modules);
        }

        $output = preg_replace('~</body>~', $jsString . '</body>', $output);
        $output = preg_replace('~</head>~', $cssString . '</head>', $output);
        $response->setContent($output);
    }

    /**
     * Returns our config object.
     *
     * @return HeroResourceFilterConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Build the viewpath for the global action.
     *
     * @param AgaviExecutionContainer $container
     *
     * @return string
     */
    protected function buildViewPath(array $executionData)
    {
        $module = strtolower($executionData['module']);
        $action = strtolower($executionData['action']);
        $viewParts = explode('/', $executionData['view']);
        $view = strtolower(
            str_replace($viewParts[0], '', array_pop($viewParts))
        );
        return implode('.', array($module, $action, $view));
    }

    /**
     * Load all scripts to be deployed for the given viewpath.
     *
     * @param string $viewpath
     *
     * @return array Where the first index is a js-script collection and the second a css collection.
     */
    protected function loadResources()
    {
        $scripts = array();
        $styles = array();
        $binaries = array();

        $directories = $this->getResourceDirectories();

        foreach($directories as $dir)
        {
            $scripts = array_merge($scripts, glob($dir.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'*'));
            $styles = array_merge($styles, glob($dir.DIRECTORY_SEPARATOR.'styles'.DIRECTORY_SEPARATOR.'*'));
            $binaries = array_merge($binaries, glob($dir.DIRECTORY_SEPARATOR.'binaries'.DIRECTORY_SEPARATOR.'*'));
        }

    return array($scripts, $styles, $binaries);
    }

    /**
     * Determines the directories from where resource files will be loaded.
     *
     * @return array A List of absolute directory paths
     */
    protected function getResourceDirectories()
    {
        $modulesDirectory = AgaviConfig::get('core.modules_dir');
        $modules = array_unique(static::$modules[$this->curOutputType]);
        $resourceDirectories = array();

        foreach($modules as $module)
        {
            $resourceDirectories[] = $modulesDirectory
                . DIRECTORY_SEPARATOR
                . $module
                . DIRECTORY_SEPARATOR
                . 'resources';
        }

        $resourceDirectories[] = AgaviConfig::get('core.app_dir')
            . DIRECTORY_SEPARATOR
            . 'resources';

        return $resourceDirectories; 
    }

    /**
     * Identifies the actual paths to the script and style paths that will be used.
     *
     * @param array
     *
     * @return array
     */
    protected function findFilePaths($files)
    {
        $modules_dir = AgaviConfig::get('core.module_dir');
        $global_resources_dir = AgaviConfig::get('core.app_dir') . DIRECTORY_SEPARATOR . 'resources';

        $modules = array();
        foreach (self::$views2Deploy as $view)
        {
            $modules[] = $view['module'];
        }

        $found_files = array();

        foreach($files as $file)
        {
            foreach($modules as $module)
            {
                $path_in_module = $modules_dir
                    . DIRECTORY_SEPARATOR
                    . $module
                    . DIRECTORY_SEPARATOR
                    . 'resources'
                    . DIRECTORY_SEPARATOR
                    . $file;

                if(is_readable($path_in_module))
                {
                    if (!in_array($path_in_module, $found_files))
                    {
                        $found_files[] = $path_in_module;
                        continue 2; //proceed with next $file
                    }
                }
                
                $global_path = $global_resources_dir . DIRECTORY_SEPARATOR . $file;
                if (is_readable($global_path))
                {
                #    $found_files[]
                }

            }
            
        }

        return $found_files;
    }

    /**
     * Takes a list of javascripts and packs them into one file.
     *
     * @param array $scripts
     *
     * @return array
     */
    protected function packJavascripts(array $scripts)
    {
        $deployHash = $this->calculateDeployHash($scripts);
        $pubDir = $this->config->get(HeroResourceFilterConfig::CFG_PUB_DIR);
        $deployPath = $this->config->getJsCacheDir() . DIRECTORY_SEPARATOR . $deployHash . '.js';
        $pubPath = substr(str_replace($pubDir, '', $deployPath), 1);

        if (!file_exists($deployPath))
        {
            $script_packer = new HeroScriptPacker($this->config);
            $packedJs = $script_packer->pack($scripts, 'js', $pubDir);
        }
        return array($pubPath);
    }

    /**
     * Takes a list of stylesheets and packs them into one file.
     *
     * @param array $scripts
     *
     * @return array
     */
    protected function packStylesheets(array $scripts)
    {
        $deployHash = $this->calculateDeployHash($scripts);
        $pubDir = $this->config->get(HeroResourceFilterConfig::CFG_PUB_DIR);
        $deployPath = $this->config->getCssCacheDir() . DIRECTORY_SEPARATOR . $deployHash . '.css';
        $pubPath = substr(str_replace($pubDir, '', $deployPath), 1);

        if (!file_exists($deployPath))
        {
            $script_packer = new HeroScriptPacker();
            $packedCss = $script_packer->pack(
                $this->adjustRelativeCssPaths($scripts), 'css'
            );
            file_put_contents($deployPath, $packedCss);
        }
        return array($pubPath);
    }

    /**
     * Takes a list of stylesheets and adjusts all relative filesystem paths
     * to still work when the affected style defionitions are moved to another fs-location.
     *
     * @param array $scripts
     *
     * @return array
     */
    protected function adjustRelativeCssPaths(array $cssFiles)
    {
        $pubDir = $this->config->get(HeroResourceFilterConfig::CFG_PUB_DIR);
        $cacheDir = realpath($this->config->getCssCacheDir()) . DIRECTORY_SEPARATOR;
        $cacheRelPath = substr(str_replace($pubDir, '', $cacheDir), 1);
        $stylesheets = array();
        foreach ($cssFiles as $cssFile)
        {
            $replaceCallback = function (array $matches) use ($pubDir, $cssFile, $cacheRelPath)
            {
                $dirName = dirname($cssFile) . DIRECTORY_SEPARATOR;
                $srcRelpath = str_replace($pubDir, '', $dirName);
                $srcDepth = count(explode(DIRECTORY_SEPARATOR, $srcRelpath)) - 1;
                $cacheDepth = count(explode(DIRECTORY_SEPARATOR, $cacheRelPath)) - 1;
                $newPath = '';

                if ($srcDepth < $cacheDepth)
                {
                    for ($i = $cacheDepth - $srcDepth; $i > 0; $i--)
                    {
                        $newPath .= '../';
                    }
                }

                $newPath .= $matches[1];
                if ($srcDepth > $cacheDepth)
                {
                    for ($i = $srcDepth - $cacheDepth; $i > 0; $i--)
                    {
                        $newPath = substr($newPath, strpos($newPath, '../'));
                    }
                }
                return sprintf("url('%s')", $newPath);
            };

            $adjustedCss = preg_replace_callback(
                '#url\([\'"](?!http|/|data)(.*?)[\'"]\)#i',
                $replaceCallback,
                file_get_contents($pubDir . DIRECTORY_SEPARATOR . $cssFile)
            );
            $tmpPath = tempnam(sys_get_temp_dir(), 'css_');
            file_put_contents($tmpPath, $adjustedCss);
            $stylesheets[] = $tmpPath;
        }
        return $stylesheets;
    }

    /**
     * Calculate the deploy hash for the given script collection.
     *
     * @param array $scripts
     *
     * @return string
     */
    protected function calculateDeployHash(array $scripts)
    {
        $lastModified = 0;
        $hashBase = '';
        foreach ($scripts as $script)
        {
            $pubDir = $this->config->get(HeroResourceFilterConfig::CFG_PUB_DIR);
            $mTime = filemtime($pubDir . DIRECTORY_SEPARATOR . $script);
            $hashBase .= $script;

            if ($lastModified < $mTime)
            {
                $lastModified = $mTime;
            }
        }
        return sha1($hashBase . $lastModified);
    }

    /**
     * Add the given stylesheet file collection to our markup,
     * one new DOMElement per given script.
     *
     * @param array $stylesheets
     */
    protected function renderStylesheets(array $stylesheets)
    {
        $script_tpl = "        <link rel='stylesheet' type='text/css' href='%1\$s' />\n";
        $scripts_string = PHP_EOL;
        foreach ($stylesheets as $stylesheet)
        {
            $scripts_string .= sprintf($script_tpl, $stylesheet);
        }
        return $scripts_string;
    }

    /**
     * Add the given javascripts file collection to our markup,
     * one new DOMElement per given script.
     *
     * @param array $javascripts
     */
    protected function renderJavascripts(array $javascripts)
    {
        $script_tpl = "    <script type='text/javascript' src='%1\$s'></script>\n";
        $scripts_string = PHP_EOL;
        foreach ($javascripts as $javascript)
        {
            $scripts_string .= sprintf($script_tpl, $javascript);
        }
        return $scripts_string;
    }
}

?>
