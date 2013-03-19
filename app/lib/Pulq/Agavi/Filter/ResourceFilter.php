<?php

namespace Pulq\Agavi\Filter;

/**
 * The ResourceFilter is responseable for detecting required scripts and deploying them for your view.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class ResourceFilter extends \AgaviFilter implements \AgaviIGlobalFilter
{
    public static function addModule($moduleName, $outputType)
    {
        if (isset(static::$modules[$outputType]) && in_array($moduleName, static::$modules[$outputType]))
        {
            return;
        }
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
     * @var ProjectResourceFilterConfig
     */
    protected $config;

    /**
     * Initialize the model, hence setup our config.
     *
     * @param AgaviContext $context
     * @param array $parameters
     */
    public function initialize(\AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);

        $this->config = new ResourceFilterConfig($parameters);
    }

    /**
     * Add the scripts for all executed html views.
     *
     * @param AgaviFilterChain A FilterChain instance.
     * @param AgaviExecutionContainer The current execution container.
     */
    public function execute(\AgaviFilterChain $filterChain, \AgaviExecutionContainer $container)
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
        if (! $this->config->isOutputTypeSupported($this->curOutputType))
        {
            // ot not supported, log to info or debug?
            return FALSE;
        }

        if ($this->config->isCachingEnabled())
        {
            $packer = new ResourcePacker(self::$modules, $this->curOutputType, $this->config);
            $packer->pack();
        }

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
        $baseDir = $this->config->getBaseDir();
        $pubDir = $this->config->getPubDir() . '/';

        $relativeBasePath = str_replace($pubDir, '', $baseDir);
        $baseUrl = \AgaviContext::getInstance()->getRouting()->getBaseHref() . $relativeBasePath;
        $resourceBaseUrl = 
            $this->config->isCachingEnabled() 
            ? $baseUrl . '/cache/'
            : $baseUrl . '/deploy/';

        $resourcesBaseDir = 
            $this->config->isCachingEnabled() 
            ? $this->config->getCacheDir()
            : $this->config->getDeployDir();
        
        $files = ResourcePacker::sortedGlob(
            $resourcesBaseDir.DIRECTORY_SEPARATOR.'_global'.DIRECTORY_SEPARATOR.$subdirectory,
            $this->curOutputType
        );

        foreach(static::$modules[$this->curOutputType] as $module)
        {
            $moduleCachePath = $resourcesBaseDir
                . DIRECTORY_SEPARATOR
                . $module
                . DIRECTORY_SEPARATOR
                . $subdirectory;

            $files = array_merge(
                $files, 
                ResourcePacker::sortedGlob($moduleCachePath, $this->curOutputType)
            );
        }
        $urls = array();

        foreach($files as $file)
        {
            if (! $this->config->isCachingEnabled())
            {
                $file .= '?cb='.filemtime($file);
            }
            $urls[] = $resourceBaseUrl.str_replace($resourcesBaseDir.DIRECTORY_SEPARATOR, '', $file);
        }

        return $urls;
    }
}
