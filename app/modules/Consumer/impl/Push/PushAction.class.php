<?php

use Pulq\Consumer\Agavi\Action\BaseAction;
use \AgaviConfig;

use Pulq\Consumer\Handlers\DocumentHandler;
use Pulq\Services\AssetService;

class Consumer_PushAction extends BaseAction
{
    public function executeWrite(AgaviRequestDataHolder $rd)
    {
        $files = $rd->getFiles();
        $document = $files['put_file'];

        $id = $rd->getParameter('id');

        $handler = DocumentHandler::create($id, $document);

        $handler->saveDocument();

        return 'Success';
    }

    public function executeRemove(AgaviRequestDataHolder $rd)
    {
        $id = $rd->getParameter('id');

        $asset_service = new AssetService();
        $asset = $asset_service->getById($id);

        $handler = DocumentHandler::create($id, $asset->toArray('detail'));

        $handler->deleteDocument();

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
