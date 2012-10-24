<?php

abstract class BaseFlowTest extends AgaviFlowTestCase
{
    public function setUpNews()
    {
        $index = AgaviContext::getInstance()->getDatabaseManager()->getDatabase('Localnews.Read')->getResource();

        $type = new Elastica_Type($index, 'localnews-newsitem');

        $data = file_get_contents(dirname(__FILE__).'/../../fixtures/News/news.items.json');
        
        $data = json_decode($data, true);

        foreach ($data as $document)
        {
            $response = $type->addDocument(new Elastica_Document($document['_id'], $document));
        }

        $index->refresh();
    }

    public function setUpMovies()
    {
        $index = AgaviContext::getInstance()->getDatabaseManager()->getDatabase('Movies.Read')->getResource();

        $movieType = new Elastica_Type($index, 'movies-movie');
        $theaterType = new Elastica_Type($index, 'movies-theater');

        $data = file_get_contents(dirname(__FILE__).'/../../fixtures/Movies/movies.items.json');
        
        $data = json_decode($data, true);

        foreach ($data as $document)
        {
            if ($document['type'] === 'FrontendMovieDocument')
            {
                $type = $movieType;
            }
            else if ($document['type'] === 'FrontendTheaterDocument')
            {
                $type = $theaterType;
            }

            $response = $type->addDocument(new Elastica_Document($document['_id'], $document));
        }

        $index->refresh();
    }
}

