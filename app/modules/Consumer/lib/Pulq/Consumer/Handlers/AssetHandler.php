<?php

namespace Pulq\Consumer\Handlers;

use Pulq\Exceptions\AssetException;
use Pulq\Services\AssetService;
use Pulq\Data\Asset;

class AssetHandler extends DocumentHandler
{
    protected $asset_directory;

    protected function __construct($id, $type, array $document)
    {
        parent::__construct($id, $type, $document);

        $this->asset_service = new AssetService();
        $asset = Asset::fromArray($this->document);

        $this->asset_path = $this->asset_service->getAssetPath($asset);
    }

    public function saveDocument()
    {
        if (!is_dir(dirname($this->asset_path))) {
            mkdir(dirname($this->asset_path), 0755, $recursive = true);
        }

        $result = file_put_contents($this->asset_path, base64_decode($this->document['data']));

        if (false === $result) {
            throw new AssetException("Could not write file $this->asset_path");
        }

        unset($this->document['data']);

        parent::saveDocument();
    }

    public function deleteDocument()
    {
        if (file_exists($this->asset_path)) {
            $result = unlink($this->asset_path);
            if (false === $result) {
                throw new AssetException("Could not delete file $this->asset_path");
            }
        }

        parent::deleteDocument();
    }
}
