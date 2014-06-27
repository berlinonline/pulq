<?php

namespace Pulq\Consumer\Handlers;

use \AgaviConfig;
use \AgaviContext;
use \Exception;

use Elastica\Document;

class DocumentHandler
{
    protected $database;
    protected $index_name;

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

        $this->database = AgaviContext::getInstance()
            ->getDatabaseManager()->getDatabase($database_name);
    }

    public function saveDocument()
    {
        $params = array(
            "index" => $this->database->getIndexName(),
            "type" => $this->document_type,
            "id" => $this->document_id,
            "body" => $this->document,
        );

        $this->database->getConnection()->index($params);
    }

    public function deleteDocument()
    {
        $params = array(
            "index" => $this->database->getIndexName(),
            "type" => $this->document_type,
            "id" => $this->document_id,
        );

        $this->database->getConnection()->delete($params);
    }
}
