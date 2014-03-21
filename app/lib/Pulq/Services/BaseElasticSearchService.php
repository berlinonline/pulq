<?php

namespace Pulq\Services;
use Pulq\Data\DataObjectSet;
use Pulq\Exceptions\NotFoundException;
use Elastica\ResultSet;
use Elastica\Query;
use Elastica\Filter;

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
        $resultData = $this->executeQuery(Query::create($query), false);

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

    public function getAll()
    {
        $query = new Query\MatchAll();

        $resultData = $this->executeQuery(Query::create($query));

        $set = $this->extractFromResultSet($resultData);

        return $set;
    }

    protected function getType()
    {
        return $this->index->getType($this->es_type);
    }

    protected function executeQuery(Query $query, $use_live_filter = true)
    {
        $bool_filter = new Filter\Bool();
        $bool_filter->addMust($this->getDefaultFilter());

        if ($use_live_filter) {
            $live_query = new Query\Field('live', "true");
            $live_filter = new Filter\Query($live_query);
            $bool_filter->addMust($live_filter);
        }

        if ($query->hasParam('filter')) {
            $existing_filter = $query->getParam('filter');
            $bool_filter->addMust($existing_filter);
        }

        $query->setFilter($bool_filter);
        $query->setSize(100000);

        #echo json_encode($query->toArray());die;

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

    protected function getDefaultFilter() {
        return new Filter\MatchAll();
    }
}
