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
                'id',
                'url',
            ),
            'detail' => array(
                'id',
                'url',
                'mime',
                'filename'
            )
        );
    }

    public static function fromArray(array $data = array())
    {
        if (isset($data['id']) && !isset($data['_id']))
        {
            $data['_id'] = $data['id'];
        }

        if (isset($data['_id']) && !isset($data['id']))
        {
            $data['id'] = $data['_id'];
        }

        return new static($data);
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
        return $this->getUrl();
    }
}

