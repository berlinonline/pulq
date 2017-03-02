<?php

namespace Pulq\Services;

use Pulq\Exceptions\AssetException;
use Pulq\Data\Asset;

class AssetService extends BaseElasticSearchService
{
    protected $asset_directory;

    protected $es_index = "default";
    protected $data_object_class = "Pulq\Data\Asset";
    protected $es_type = "asset";

    public function __construct()
    {
        parent::__construct();
        $this->asset_directory = \AgaviConfig::get('core.asset_directory');

        if (!$this->asset_directory) {
            throw new AssetException("No asset directory is configured or directory doesn't exist!");
        }
    }

    public function getAssetDirectory()
    {
        return $this->asset_directory;
    }

    protected function getRelativeAssetPath(Asset $asset)
    {

        $id = $asset->getId();

        $filename = $asset->getFilename();
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        list($type, $numeric_id) = explode('-', $id);
        $path_parts = str_split($numeric_id, 2);

        return implode('/', $path_parts) . '/' . $id . '/' . $filename;

    }

    public function getAssetPath(Asset $asset)
    {
        return $this->getAssetDirectory() . '/' . $this->getRelativeAssetPath($asset);
    }

    public function getAssetUrl(Asset $asset)
    {
        $base_href = \AgaviContext::getInstance()->getRouting()->getBaseHref();
        $asset_url_path = \AgaviConfig::get('core.asset_url_path');
        return $base_href . $asset_url_path . '/'. $this->getRelativeAssetPath($asset);
    }

    public static function getConverjonUrl($url, array $params)
    {
        $params['url'] = urlencode($url);

        $converjon_url = \AgaviConfig::get('converjon.base_url', "http://localhost/") . "?";

        $paramsString = '';

        foreach ($params as $key => $value)
        {
            $paramsString .= ($paramsString ? '&' : '') .  $key . '=' . $value;
        }

        $converjon_url .= $paramsString;

        return $converjon_url;
    }
}
