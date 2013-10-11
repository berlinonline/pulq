<?php

namespace Pulq\Services;

use \AgaviConfig;
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

    public function getAssetPathById($id)
    {
        list($type, $numeric_id) = explode('-', $id);
        $path_parts = str_split($numeric_id, 2);

        return $this->getAssetDirectory() . '/' . implode('/', $path_parts) . '/' . $id;
    }
}
