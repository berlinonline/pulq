<?php

namespace Pulq\Data;

use Pulq\Services\AssetService;

class Asset extends BaseDataObject
{
    protected $_id;
    protected $filename;
    protected $mime;

    public function getArrayScopes()
    {
        return array(
            'list' => array(
                'url',
            ),
            'detail' => array(
                'url',
                'mime',
                'filename'
            )
        );
    }

    public function getId()
    {
        return $this->_id;
    }

    protected function getUrl() {
        $asset_service = new AssetService();

        return $asset_service->getAssetUrl($this);
    }

    public function __toString()
    {
        return $this->getSrc();
    }
}

