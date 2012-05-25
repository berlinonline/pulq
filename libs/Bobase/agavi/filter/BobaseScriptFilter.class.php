<?php

/**
 * The BobaseScriptFilter is responseable for detecting required scripts and deploying them for your view.
 *
 * @version         $Id: BobaseScriptFilter.class.php 1065 2012-04-05 12:05:39Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Bobase
 * @subpackage      Agavi/Filter
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class BobaseScriptFilter extends AgaviFilter implements AgaviIGlobalFilter
{
    protected static $views2Deploy = array();

    public static function addView($moduleName, $actionName, $viewName, $outputType)
    {
        $viewHash = sha1($moduleName.$actionName.$viewName);
        if (! isset(self::$views2Deploy[$viewHash]))
        {
            self::$views2Deploy[$viewHash] = array(
                'module'     => $moduleName,
                'action'     => $actionName,
                'view'       => $viewName,
                'outputType' => $outputType
            );
        }
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
     * Holds an array of chars that have a special meaning when defining 'viewpaths'.
     * This chars will be mutated before creating a regex from their surrounding string.
     *
     * @var array
     */
    protected static $viewPathSearch = array('.', '*');

    /**
     * Holds the 'mutated' versions of our special 'viewpath' chars.
     *
     * @var array
     */
    protected static $viewPathReplace = array('\.', '.*');

    /**
     * @var DOMDocument Our (X)HTML document.
     */
    protected $doc;

    /**
     * Our XPath instance for the document.
     *
     * @var DOMXPath
     */
    protected $xpath;

    /**
     * The XML NS prefix we're working on with XPath, including
     * a colon (or empty string if document has no NS).
     *
     * @var string
     */
    protected $xmlnsPrefix = '';

    /**
     * An array holding all scripts that have been loaded so far.
     *
     * @var array
     */
    protected $loadedScripts = array();

    /**
     * An array holding all packages that have been loaded so far.
     *
     * @var array
     */
    protected $loadedPackages = array();

    /**
     * Holds our config object.
     *
     * @var BobaseScriptFilterConfig
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

        $this->config = new BobaseScriptFilterConfig($parameters);
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
/*list($usec3, $sec3) = explode(" ",microtime());
$r1 = ($usec3/1000 + $sec3*1000);*/
        if (! $response->isContentMutable() || ! ($output = $response->getContent()))
        {
            // throw exception? we cant really live without our scripts...
            return FALSE;
        }

        $curOutputType = $response->getOutputType()->getName();
        if (!$this->config->isOutputTypeSupported($curOutputType))
        {
            // ot not supported, log to info or debug?
            return FALSE;
        }

        list($javascripts, $stylesheets) = $this->loadScripts();

        $jsString = '';
        $cssString = '';
        if ($this->config->isPackingEnabled())
        {
            $jsString = $this->renderJavascripts(
                $this->packJavascripts($javascripts)
            );
            $cssString = $this->renderStylesheets(
                $this->packStylesheets($stylesheets)
            );
        }
        else
        {
            $cssString = $this->renderStylesheets($stylesheets);
            $jsString = $this->renderJavascripts($javascripts);
        }
        // Find inline stuff and append to end.
        $inlineJs = array();
        if (preg_match('~<script type="text/javascript" (?:src=".+\.js")?>.*</script>~is', $output, $inlineJs))
        {
            foreach ($inlineJs as $inline)
            {
                $output = str_replace($inline, '', $output);
                $jsString .= PHP_EOL . $inline;
            }
        }

        $output = preg_replace('~</body>~', $jsString . '</body>', $output);
        $output = preg_replace('~</head>~', $cssString . '</head>', $output);
        $response->setContent($output);
/*list($usec4, $sec4) = explode(" ",microtime());
$r2 = ($usec4/1000 + $sec4*1000);
error_log("<BobaseScriptFilter>" . ($r2 - $r1) . "</BobaseScriptFilter>");*/
    }

    /**
     * Returns our config object.
     *
     * @return BobaseScriptFilterConfig
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
    protected function loadScripts()
    {
        $deployments = array();
        $javascripts = array();
        $stylesheets = array();

        foreach(self::$views2Deploy as $executionData)
        {
            $viewPath = $this->buildViewPath($executionData);
            $deployments[] = $this->loadDeployData($viewPath);
        }

        foreach ($deployments as $deployment)
        {
            foreach ($deployment['packages'] as $packageName)
            {
                $package = $this->config->getPackageData($packageName);
                foreach ($package['javascripts'] as $javascript)
                {
                    if (! in_array($javascript, $javascripts))
                    {
                        $javascripts[] = $javascript;
                    }
                }
                foreach ($package['stylesheets'] as $stylesheet)
                {
                    if (! in_array($stylesheet, $stylesheets))
                    {
                        $stylesheets[] = $stylesheet;
                    }
                }
            }
            $javascripts = array_merge($javascripts, $deployment['javascripts']);
            $stylesheets = array_merge($stylesheets, $deployment['stylesheets']);
        }
        return array($javascripts, $stylesheets);
    }

    /**
     * Load the deploy data (packages, css- and js-script-files) for the given viewpath.
     *
     * @param string $viewpath
     *
     * @return array
     */
    protected function loadDeployData($viewpath)
    {
        $affectedPackages = array();
        $affectedJavascripts = array();
        $affectedStylesheets = array();
        foreach ($this->config->getDeployments() as $curViewpath => $deploymentInfo)
        {
            $escapedPath = str_replace(
                self::$viewPathSearch, self::$viewPathReplace, $curViewpath
            );
            $pattern = sprintf('#^%s$#is', $escapedPath);

            if (preg_match($pattern, $viewpath))
            {
                foreach ($deploymentInfo['packages'] as $packageName)
                {
                    $this->loadPackage($packageName, $affectedPackages);
                }

                foreach ($deploymentInfo['javascripts'] as $javascript)
                {
                    if (! in_array($javascript, $affectedJavascripts))
                    {
                        $affectedJavascripts[] = $javascript;
                    }
                }

                foreach ($deploymentInfo['stylesheets'] as $stylesheet)
                {
                    if (! in_array($stylesheet, $affectedStylesheets))
                    {
                        $affectedStylesheets[] = $stylesheet;
                    }
                }
            }
        }

        $deployData = array(
            'javascripts' => $affectedJavascripts,
            'stylesheets' => $affectedStylesheets,
            'packages'    => array()
        );
        /**
         * Make sure we have our loaded packages in the exact same order
         * as defined inside our scripts.xml config.
         */
        foreach ($this->config->getPackageNames() as $packageName)
        {
            if (in_array($packageName, $affectedPackages))
            {
                $deployData['packages'][] = $packageName;
            }
        }
        return $deployData;
    }

    /**
     * Load the given package by name.
     * The package is added to the passed list of $loadedPackages.
     *
     * @param string $packageName
     * @param array $loadedPackages
     */
    protected function loadPackage($packageName, array & $loadedPackages)
    {
        if (! in_array($packageName, $loadedPackages))
        {
            $package = $this->config->getPackageData($packageName);
            $loadedPackages[] = $packageName;

            foreach ($package['deps'] as $depPackage)
            {
                $this->loadPackage($depPackage, $loadedPackages);
            }
        }
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
/* list($usec3, $sec3) = explode(" ",microtime());
$r1 = ($usec3/1000 + $sec3*1000);*/
        $deployHash = $this->calculateDeployHash($scripts);
        $pubDir = $this->config->get(BobaseScriptFilterConfig::CFG_PUB_DIR);
        $deployPath = $this->config->getJsCacheDir() . DIRECTORY_SEPARATOR . $deployHash . '.js';
        $pubPath = substr(str_replace($pubDir, '', $deployPath), 1);

        if (!file_exists($deployPath))
        {
            $script_packer = new BobaseScriptPacker();
            $packedJs = $script_packer->pack($scripts, 'js', $pubDir);
            //array_map("unlink", glob($this->config->getJsCacheDir() . '/*.js')); // remove all prev caches
            file_put_contents($deployPath, $packedJs);
        }
/*list($usec4, $sec4) = explode(" ",microtime());
$r2 = ($usec4/1000 + $sec4*1000);
error_log("<JAVASCRIPT PACKING>" . ($r2 - $r1) . "</JAVASCRIPT PACKING>"); */
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
/*list($usec3, $sec3) = explode(" ",microtime());
$r1 = ($usec3/1000 + $sec3*1000);*/
        $deployHash = $this->calculateDeployHash($scripts);
        $pubDir = $this->config->get(BobaseScriptFilterConfig::CFG_PUB_DIR);
        $deployPath = $this->config->getCssCacheDir() . DIRECTORY_SEPARATOR . $deployHash . '.css';
        $pubPath = substr(str_replace($pubDir, '', $deployPath), 1);

        if (!file_exists($deployPath))
        {
            $script_packer = new BobaseScriptPacker();
            $packedCss = $script_packer->pack(
                $this->adjustRelativeCssPaths($scripts), 'css'
            );
            //array_map("unlink", glob($this->config->getCssCacheDir() . '/*.css')); // remove all prev caches
            file_put_contents($deployPath, $packedCss);
        }
/*list($usec4, $sec4) = explode(" ",microtime());
$r2 = ($usec4/1000 + $sec4*1000);
error_log("<CSS PACKING>" . ($r2 - $r1) . "</CSS PACKING>");*/
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
        $pubDir = $this->config->get(BobaseScriptFilterConfig::CFG_PUB_DIR);
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
            // @todo replace all possible @import and possible urls.
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
/*list($usec, $sec) = explode(" ",microtime());
$now = ($usec/1000 + $sec*1000);*/
        foreach ($scripts as $script)
        {
            $pubDir = $this->config->get(BobaseScriptFilterConfig::CFG_PUB_DIR);
            $mTime = filemtime($pubDir . DIRECTORY_SEPARATOR . $script);
            $hashBase .= $script;

            if ($lastModified < $mTime)
            {
                $lastModified = $mTime;
            }
        }
/*list($usec, $sec) = explode(" ",microtime());
$then = ($usec/1000 + $sec*1000);
error_log("<HashCalculation Hash='".sha1($hashBase . $lastModified)."'>" . ($then - $now) . "</HASH Calculation>");*/
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