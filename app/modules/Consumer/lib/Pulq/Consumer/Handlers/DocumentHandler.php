<?php

namespace Pulq\Consumer\Handlers;

use \AgaviConfig;
use \AgaviContext;
use \Exception;

use Elastica\Document;

class DocumentHandler
{
    public function saveDocument($id, array $document)
    {
        $database_name = AgaviConfig::get('consumer.database', 'default');

        $database = AgaviContext::getInstance()
            ->getDatabaseManager()->getDatabase($database_name);

        $document_type = $this->getTypeNameFromId($id);

        $es_index = $database->getResource();
        $es_type = $es_index->getType($document_type);

        $es_document = new Document($id, $document);
        $es_type->addDocument($es_document);
    }

    public function deleteDocument($id)
    {
        $database_name = AgaviConfig::get('consumer.database', 'default');

        $database = AgaviContext::getInstance()
            ->getDatabaseManager()->getDatabase($database_name);

        $document_type = $this->getTypeNameFromId($id);

        $es_index = $database->getResource();
        $es_type = $es_index->getType($document_type);

        $es_type->deleteById($id);
    }

    protected function getTypeNameFromId($id)
    {
        $id_parts = explode('-', $id);

        if (count($id_parts) !== 2) {
            throw new Exception("ID '$id' is malformed. expected is something like 'article-42' or 'asset-51'." );
        }

        return $id_parts[0];
    }
}
