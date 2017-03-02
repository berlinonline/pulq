<?php

use Pulq\Consumer\Agavi\Action\BaseAction;
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

        if (preg_match('/^asset-/', $id)) {
            $asset_service = new AssetService();
            $document_data = $asset_service->getById($id)->toArray('detail');
        } else {
            $document_data = array();
        }

        $handler = DocumentHandler::create($id, $document_data);

        $handler->deleteDocument();

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
