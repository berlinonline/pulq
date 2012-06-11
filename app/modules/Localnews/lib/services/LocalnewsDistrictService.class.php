<?php

class LocalnewsDistrictService extends ProjectBaseService
{

    public function __construct()
    {
        $this->index = AgaviContext::getInstance()->getDatabaseManager()->getDatabase('News.Read')->getResource();

    }

    /**
     * Generates some dummy data as long as there's no working model layer.
     */
    public function getDistricts()
    {
        $ro = AgaviContext::getInstance()->getRouting();

        $query = Elastica_Query::create(null);
        $facetname = 'district-facet';
        $facet = new Elastica_Facet_Terms($facetname);
        $facet->setFields(array(
            'location.district.raw',
        ));
        $facet->setSize(2000);
        $query->addFacet($facet);
        $esType = $this->index->getType('localnews-newsitem');
        $resultData = $esType->search($query);
        $districtFacets = $resultData->getFacets();

        $districts = array();

        foreach($districtFacets['district-facet']['terms'] as $term)
        {
            $name = $term['term'];
            $districts[] = array(
                'name' => $name,
                'url' => $ro->gen('localnews.bydistrict', array(
                    'district' => $name,
                ))
            );
        }

        return $districts;
    }

    
    public function getDistrictByName($name)
    {
        foreach($this->getDistricts() as $district)
        {
            if ($district['name'] === $name)
            {
                return $district;
            }
        }

        throw new DistrictNotFoundException();
    }

}
