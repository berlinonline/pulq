<?php

namespace Pulq\Consumer\Handlers;

use \AgaviConfig;
use \AgaviContext;
use \Exception;

use Elastica\Document;

class DocumentHandler
{
    protected $document_id;
    protected $document_type;
    protected $document = array();

    public static function create($id, array $document)
    {
        $id_parts = explode('-', $id);
        $type = $id_parts[0];

        if ($type === 'asset') {
            return new AssetHandler($id, $type, $document);
        } else {
            return new static($id, $type, $document);
        }
    }

    protected function __construct($id, $type, array $document)
    {
        $this->document_id = $id;
        $this->document_type = $type;

        $this->document = $document;

        $database_name = AgaviConfig::get('consumer.database', 'default');

        $database = AgaviContext::getInstance()
            ->getDatabaseManager()->getDatabase($database_name);

        $this->es_index = $database->getResource();
    }

    public function saveDocument()
    {
        $es_type = $this->es_index->getType($this->document_type);

        $es_document = new Document($this->document_id, $this->document);

        $es_type->addDocument($es_document);
    }

    public function deleteDocument()
    {
        $es_type = $this->es_index->getType($this->document_type);

        $es_type->deleteById($this->document_id);
    }
}
