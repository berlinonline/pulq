<?php

namespace Pulq\Services;
use Pulq\Data\DataObjectSet;
use Elastica\ResultSet;
use Elastica\Query\AbstractQuery;

abstract class BaseElasticSearchService extends BaseService {
    protected $es_index = null;
    protected $data_object_class = null;
    protected $es_type = null;

    public function __construct()
    {
        $this->index = \AgaviContext::getInstance()->getDatabaseManager()->getDatabase($this->es_index)->getResource();
    }

    protected function getType()
    {
        return $this->index->getType($this->es_type);
    }

    protected function executeQuery(AbstractQuery $query)
    {
        return $this->getType()->search($query);
    }

    protected function extractFromResultSet(ResultSet $resultSet)
    {
        $data_objects = array();

        foreach($resultSet->getResults() as $result)
        {
            $data = $result->getData();

            $class_name = $this->data_object_class;
            $data_objects[] = $class_name::fromArray($data);
        }

        $set = new DataObjectSet($data_objects);
        $set->setTotalCount($resultSet->getTotalHits());

        return $set;
    }
}
