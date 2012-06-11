<?php

class LocalnewsService extends ProjectBaseService
{
    protected $index = null;

    public function __construct()
    {
        $this->index = AgaviContext::getInstance()->getDatabaseManager()->getDatabase('News.Read')->getResource();

    }

    public function getLatestNews($limit =50)
    {
        $newsitems = array();

        $query = $this->getBaseNewsQuery($limit);
        $esType = $this->index->getType('localnews-newsitem');
        $resultData = $esType->search($query);

        return $this->extractNewsitemsFromResultSet($resultData);
    }

    protected function getBaseNewsQuery($limit = 5)
    {

        $query = Elastica_Query::create(
            new Elastica_Query_MatchAll()
        );
        $query->setLimit($limit);
        $query->setSort(array(
            'publishDate' => array('order' => 'desc')
        ));
    
        return $query;
    }

    public function getNewsByDistrict($district, $limit = 5)
    {
        $newsitems = array();

        $query = $this->getBaseNewsQuery($limit);
        $query->setFilter(
            new Elastica_Filter_Term(
                array('location.district.raw' => $district['name'])
            )
        );
        $esType = $this->index->getType('localnews-newsitem');
        $resultData = $esType->search($query);

        return $this->extractNewsitemsFromResultSet($resultData);
    }

    protected function extractNewsitemsFromResultSet(Elastica_ResultSet $resultSet)
    {
        $newsitems = array();

        foreach($resultSet->getResults() as $result)
        {
            $data = $result->getData();

            $newsitems[] = array(
                'title' => $data['title'],
                'teaser' => $data['teaser'],
                'text' => $data['text'],
                'publishDate' => date_format(new DateTime($data['publishDate']), 'd.m.Y - H:i') . ' Uhr',
            );
        }

        return $newsitems;
    
    }
}
