<?php

namespace Pulq\Services;
use Pulq\Data\DataObjectSet;
use Elastica\ResultSet;
use Elastica\Query;

abstract class BaseElasticSearchService extends BaseService {
    protected $es_index = null;
    protected $data_object_class = null;
    protected $es_type = null;

    public function __construct()
    {
        $this->index = \AgaviContext::getInstance()->getDatabaseManager()->getDatabase($this->es_index)->getResource();
    }

    public function getById($id)
    {
        $query = new Query\Field('_id', $id);
        $resultData = $this->executeQuery(Query::create($query));

        $set = $this->extractFromResultSet($resultData);

        if ($set->getTotalCount() < 1)
        {
            throw new NotFoundException($this->data_object_class . ' not found.');
        }

        return $set[0];
    }

    public function getByIds(array $ids)
    {
        if (empty($ids))
        {
            return new DataObjectSet(array());
        }

        $query = new Query\Field('_id', implode(' ', $ids));
        $resultData = $this->executeQuery(Query::create($query));

        $set = $this->extractFromResultSet($resultData);

        return $set;
    }

    protected function getType()
    {
        return $this->index->getType($this->es_type);
    }

    protected function executeQuery(Query $query)
    {
        return $this->getType()->search($query);
    }

    protected function extractFromResultSet(ResultSet $resultSet)
    {
        $data_objects = array();

        foreach($resultSet->getResults() as $result)
        {
            $id = $result->getId();
            $data = $result->getData();
            $data['_id'] = $id;

            $class_name = $this->data_object_class;
            $data_objects[] = $class_name::fromArray($data);
        }

        $set = new DataObjectSet($data_objects);
        $set->setTotalCount($resultSet->getTotalHits());

        return $set;
    }
}
