<?php

namespace Pulq\Services;

use \AgaviConfig;
use \AgaviContext;
use Pulq\Exceptions\AssetException;

class AssetService extends BaseService
{
    protected $asset_directory;

    public function __construct()
    {
        $this->asset_directory = AgaviConfig::get('core.asset_directory');

        if (!$this->asset_directory) {
            throw new AssetException("No asset directory is configured or directory doesn't exist!");
        }
    }

    public function getAssetDirectory()
    {
        return $this->asset_directory;
    }

    protected function getRelativeAssetPathById($id)
    {
        list($type, $numeric_id) = explode('-', $id);
        $path_parts = str_split($numeric_id, 2);

        return implode('/', $path_parts) . '/' . $id;

    }

    public function getAssetPathById($id)
    {
        return $this->getAssetDirectory() . '/' . $this->getRelativeAssetPathById($id);
    }

    public function getAssetUrlById($id)
    {
        $base_href = AgaviContext::getInstance()->getRouting()->getBaseHref();
        $asset_url_path = AgaviConfig::get('core.asset_url_path');
        return $base_href . $asset_url_path . '/'. $this->getRelativeAssetPathById($id);
    }
}
