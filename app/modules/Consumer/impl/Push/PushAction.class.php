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

        $handler = new DocumentHandler();

        $handler->saveDocument($id, $document);

        return 'Success';
    }

    public function executeRemove(AgaviRequestDataHolder $rd)
    {
        $id = $rd->getParameter('id');

        $handler = new DocumentHandler();

        $handler->deleteDocument($id);

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
