<?php

namespace Pulq\Services;
use Pulq\Data\DataObjectSet;
use Pulq\Exceptions\NotFoundException;
use \AgaviContext;
use Elasticsearch\Common\Exceptions\Missing404Exception;

abstract class BaseElasticSearchService extends BaseService {
    protected $es_index = null;
    protected $data_object_class = null;
    protected $es_type = null;

    public function __construct()
    {
        $this->database = AgaviContext::getInstance()
            ->getDatabaseManager()
            ->getDatabase($this->es_index);
        $this->es_client = $this->database->getConnection();
    }

    public function getById($id)
    {
        $params = [
            "id" => $id,
        ];

        $params = $this->addIndexAndType($params);

        try {
            $result = $this->es_client->get($params);
            $document = $result['_source'];
        } catch (Missing404Exception $exception) {
            throw new NotFoundException("Document with id $id not found");
        }

        return new $this->data_object_class($document);
    }

    public function getByIds(array $ids)
    {
        if (empty($ids))
        {
            return new DataObjectSet(array());
        }

        $params = [
            "body" => [ "ids" => $ids ],
        ];

        $params = $this->addIndexAndType($params);

        $result = $this->es_client->mget($params);
        $documents = $result['docs'];

        $data_objects = [];

        foreach($documents as $document) {
            if (!$document['found']) {
                continue;
            }

            $data_objects[] = new $this->data_object_class($document['_source']);
        }

        $set = new DataObjectSet($data_objects);
        $set->setTotalCount(count($data_objects));

        return $set;
    }

    public function getAll()
    {
        $search = [
            "filter" => ["match_all" => []],
        ];

        $resultData = $this->executeFiltered($search);

        $set = $this->extractFromResult($resultData);

        return $set;
    }

    protected function executeFiltered(array $search)
    {
        return $this->execute($search, $live_filter = true, $default_filter = true);
    }

    protected function executeUnfiltered(array $search)
    {
        return $this->execute($search, $live_filter = false, $default_filter = false);
    }

    protected function execute(array $search, $use_live_filter = true, $use_default_filter = true)
    {
        $filter = [
            "bool" => [
                "must" => [],
                "must_not" => [],
                "should" => [],
            ]
        ];

        if ($use_default_filter) {
            $filter["bool"]["must"][] = $this->getDefaultFilter();
        }

        if ($use_live_filter) {
            $filter["bool"]["must"][] = [
                "term" => ["live" => true]
            ];
        }

        if (isset($search['filter'])) {
            //include the existing filter in the MUST part of the new one.
            $filter["bool"]["must"][] = $search["filter"];
        }

        $search["filter"] = $filter;

        if (!isset($search["size"])) {
            $search["size"] = 10000;
        }

        # echo json_encode($search);die;

        $params = $this->addIndexAndType([
            "body" => $search,
        ]);

        return $this->es_client->search($params);
    }

    protected function addIndexAndType(array $params) {
        if (!isset($params['index'])) {
            $params['index'] = $this->database->getIndexName();
        }

        if (!isset($params['type'])) {
            $params['type'] = $this->es_type;
        }

        return $params;
    }

    protected function extractFromResult(array $result)
    {
        $data_objects = [];
        $hits = $result["hits"]["hits"];
        $total = $result["hits"]["total"];

        if ($total === 0) {
            return new DataObjectSet([]);
        }

        foreach($hits as $hit)
        {
            $id = $hit["_id"];
            $data = $hit["_source"];
            $data['_id'] = $id;

            $class_name = $this->data_object_class;
            $data_objects[] = $class_name::fromArray($data);
        }

        $set = new DataObjectSet($data_objects);
        $set->setTotalCount($total);

        return $set;
    }

    protected function getDefaultFilter() {
        return [
            "match_all" => [],
        ];
    }

    public function getByPreviewId($preview_id)
    {
        $search = [
            "filter" => [
                "term" => [
                    "preview_id" => $preview_id
                ]
            ]
        ];

        $resultData = $this->executeUnfiltered(search);
        $set = $this->extractFromResult($resultData);

        if ($set->getTotalCount() < 1)
        {
            throw new NotFoundException($this->data_object_class . ' not found.');
        }

        return $set[0];
    }
}
