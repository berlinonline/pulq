<?php

/**
 * Simple filter to resolve ESI statements in the response
 *
 * parameters:
 * * relative - (boolean) try to replace domain relative links in the content to fix preview
 * * baseHref - (string) full domain url to complement domain relative ESI einclude URLs
 *
 * used agavi config:
 * * memcache.domain - (string) unique memcache key prefix for the current environment
 * * memcache.server - (array) list of hostname:port strings to connect memcache
 *
 * Filter considers response headers when using memcache
 * * Expires - generate absolute TTL to specified timestamp
 * * Cache-Control - private to set TTL to 0; max-age=TTL
 *
 * Standard TTL is 120 seconds
 *
 * @author tay
 * @version $Id: BoEsiFilter.class.php 5103 2012-06-29 08:12:04Z tay $
 * @since 22.06.2012
 *
 */
class PulqEsiFilter extends AgaviFilter implements AgaviIGlobalFilter
{
    /**
     * @var MemCache
     */
    private $memcache_handle;


    /**
     * Initialize this Filter.
     *
     * @param      AgaviContext The current application context.
     * @param      array        An associative array of initialization parameters.
     *
     * @throws     <b>AgaviInitializationException</b> If an error occurs while
     *                                                 initializing this Filter.
     */
    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);

        if (AgaviConfig::has('memcache.domain'))
        {
            $servers = AgaviConfig::get('memcache.server');
            if (empty($servers))
            {
                throw new AgaviInitializationException('Config parameter array "memcache.servers" missing.');
            }

            if (!is_array($servers))
            {
                $servers = array(
                        $servers
                    );
            }

            $this->memcache_handle = $con = new Memcache;
            foreach ($servers as $server)
            {
                if (preg_match('/([\w\.\-]+):(\d+)/', $server, $m))
                {
                    $status = $con->addServer($m[1], $m[2]);
                }
                else
                {
                    $status = $con->addServer($server);
                }
                if (!$status)
                {
                    throw new AgaviInitializationException('Can not add memcached server: ' . $server);
                }
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see AgaviFilter::execute()
     */
    public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
    {
        $filterChain->execute($container);
        $response = $container->getResponse();

        if (!$response->isContentMutable() || !($output = $response->getContent()))
        {
            return;
        }

        $output = $this->processEsi($output);
        $response->setContent($output);
    }

    /**
     * simple esi procession
     *
     * supports: esi:include, esi:remove
     *
     * @param string $content
     * @return string
     */
    public function processEsi($content)
    {
        $self = $this;
        $output = preg_replace('#<esi:remove>.*?</esi:remove>#s', '', $content);
        $output =
            preg_replace_callback('#<esi:include\s.*?\bsrc="(.*?)".*?/>#s',
                function ($matches) use ($self)
                {
                    return $self->esiInclude($matches[1]);
                }, $output);
        return $output;
    }

    /**
     * perform an ESI include
     *
     * @param string $url
     * @return string
     */
    public function esiInclude($url)
    {
        if ('/' == $url[0])
        {
            $baseUrl = preg_replace('#/+$#', '',
                    $this->getParameter(
                            'baseHref',
                            $this->getContext()->getRouting()->getBaseHref()));
            $url = $baseUrl . $url;
        }

        if (FALSE === ($body = $this->cacheGet($url)))
        {
            $ch = PulqToolkit::curl_init($url, $this->getContext()->getRequest());
            curl_setopt($ch, CURLOPT_HEADER, 1);
            $response = curl_exec($ch);
            if (200 != curl_getinfo($ch, CURLINFO_HTTP_CODE))
            {
                $msg = sprintf('esi:include failed "%s" :: "%s"', $url, curl_error($ch));
                error_log($msg);
                return '<!-- ' . htmlspecialchars($msg) . ' -->';
            }

            $head = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
            $body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));

            $ttl = 120;
            if (preg_match('/^Expires: (\w\w\w,\s.*GMT)$/mi', $head, $match))
            {
                $ttl = max(0, strtotime($match[1]) - time());
            }
            if (preg_match('/^Cache-Control:(.*)/i', $head, $match))
            {
                $cc = $match[1];
                if (FALSE !== strpos($cc, 'private'))
                {
                    $ttl = 0;
                }
                elseif (preg_match('/max-age=(\d+)/i', $cc, $match))
                {
                    $ttl = $match[1];
                }
            }
            if (!$this->getParameter('relative', TRUE))
            {
                $parsed = parse_url($url);
                if ($parsed['scheme'] && $parsed['host'])
                {
                    $urlPrefix = $parsed['scheme'] . '://' . $parsed['host'];
                }
                $body = preg_replace('#="/#', '="' . $urlPrefix . '/', $body);
            }
            $this->cacheWrite($url, $body, $ttl);
        }
        return $this->processEsi($body);
    }

    /**
     * try to get content from cache
     *
     * @param unknown_type $url
     * @return mixed string on cache hit or boolean FALSE
     */
    protected function cacheGet($url)
    {
        if ($this->memcache_handle)
        {
            return $this->memcache_handle
                ->get($this->cacheKey($url, MEMCACHE_COMPRESSED));
        }
        return FALSE;
    }


    /**
     * write response to cache
     *
     * @param string $url
     * @param string $response
     * @param int $ttl
     */
    protected function cacheWrite($url, $response, $ttl)
    {
        if ($this->memcache_handle && $ttl > 30)
        {
            $this->memcache_handle
                ->set($this->cacheKey($url), $response, MEMCACHE_COMPRESSED, $ttl);
        }
    }


    /**
     * compute a internal key for caching the url
     *
     * @param string $url
     * @return string
     */
    protected function cacheKey($url)
    {
        return AgaviConfig::get('memcache.domain') . __CLASS__ . $url;
    }
}
