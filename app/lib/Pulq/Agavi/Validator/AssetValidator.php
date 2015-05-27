<?php

namespace Pulq\Agavi\Validator;
use Pulq\Exceptions\NotFoundException;

class AssetValidator extends \AgaviValidator
{
    protected function validate()
    {
        $module = $this->getData('module');
        $asset_id = $this->getData('asset');

        $service_class_map = \AgaviConfig::get('core.asset_service_map');

        if (!isset($service_class_map[$module]))
        {
            $this->throwError("invalid_module", "module");
            return false;
        }

        $service = new $service_class_map[$module];

        try
        {
            $asset = $service->getById($asset_id);
            $this->export($asset, 'asset');
            return true;
        }
        catch (NotFoundException $exception)
        {
            $this->throwError("not_found", "asset");
            return false;
        }
    }
}
