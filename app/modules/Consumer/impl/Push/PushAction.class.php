<?php

use Pulq\Consumer\Agavi\Action\BaseAction;
use \AgaviConfig;

use Pulq\Consumer\Handlers\DocumentHandler;

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

        $handler = DocumentHandler::create($id);

        $handler->deleteDocument();

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
