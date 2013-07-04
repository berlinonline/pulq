<?php

namespace Pulq\Data;

abstract class Image extends BaseDataObject
{
    protected $assetUrl;
    protected $width;
    protected $height;
    protected $copyright;
    protected $copyrightUrl;

    protected function isConverjonEnabled()
    {
        $enabled = \AgaviConfig::get('converjon.enabled', false);

        return $enabled;
    }

    protected function getConverjonUrl($url, array $params)
    {
        $params['url'] = urlencode($url);

        $host = \AgaviConfig::get('converjon.host', 'localhost');
        $port = \AgaviConfig::get('converjon.port', 80);
        $baseUrl = \AgaviConfig::get('converjon.base_path', '/');
        if ($port == 80)
        {
            $converjon_url = sprintf('http://%s%s?', $host, $baseUrl);
        }
        else
        {
            $converjon_url = sprintf('http://%s:%d%s?', $host, $port, $baseUrl);
        }

        $paramsString = '';

        foreach ($params as $key => $value)
        {
            $paramsString .= ($paramsString ? '&' : '') .  $key . '=' . $value;
        }

        $converjon_url .= $paramsString;

        return $converjon_url;
    }

    public function getArrayScopes()
    {
        return array(
            'list' => array(
                'src',
                'width',
                'height'
            ),
            'detail' => array(
                'src',
                'width',
                'height'
            )
        );
    }

    abstract protected function getAssetUrl();

    public function getSrc(array $params = array())
    {
        if ($this->isConverjonEnabled())
        {
            $url = $this->getConverjonUrl(
                rtrim(\AgaviContext::getInstance()->getRouting()->getBaseHref(), '/').$this->getAssetUrl(),
                $params
            );
        }
        else
        {
            return $this->getAssetUrl();
        }

        return $url;
    }

    public function __toString()
    {
        return $this->getSrc();
    }
}

