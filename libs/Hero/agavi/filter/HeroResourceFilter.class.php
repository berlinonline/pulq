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

        $styleTags = $this->getStyleTags();
        $scriptTags = $this->getScriptTags();

        $output = str_replace(array(
            '<!-- %%STYLESHEETS%% -->',
            '<!-- %%JAVASCRIPTS%% -->'
        ), array(
            $styleTags,
            $scriptTags
        ), $output);

        $response->setContent($output);
    }

    protected function getScriptTags()
    {
        $tags = '';

        foreach($this->getCachedAssetUrls('scripts') as $url)
        {
            $tags .= sprintf('<script type="text/javascript" src="%s"></script>'.PHP_EOL, $url);
        }

        return $tags;
    }

    protected function getStyleTags()
    {
        $tags = '';

        foreach($this->getCachedAssetUrls('styles') as $url)
        {
            $tags .= sprintf('<link rel="stylesheet" type="text/css" href="%s" />'.PHP_EOL, $url);
        }

        return $tags;
    }

    protected function getCachedAssetUrls($subdirectory)
    {
        $cacheBaseUrl = $this->getContext()->getRouting()->getBaseHref() . 'static/cache/';
        $cacheDir = $this->config->getCacheDir();

        $files = glob($cacheDir.DIRECTORY_SEPARATOR.'_global'.DIRECTORY_SEPARATOR.$subdirectory.DIRECTORY_SEPARATOR.'*');

        foreach(static::$modules[$this->curOutputType] as $module)
        {
            $moduleCachePath = $cacheDir
                . DIRECTORY_SEPARATOR
                . $module
                . DIRECTORY_SEPARATOR
                . $subdirectory
                . DIRECTORY_SEPARATOR
                . '*';
            $files = array_merge($files, glob($moduleCachePath));
        }

        $urls = array();

        foreach($files as $file)
        {
            $urls[] = $cacheBaseUrl . str_replace($cacheDir.DIRECTORY_SEPARATOR, '', $file);
        }

        return $urls;
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

}
