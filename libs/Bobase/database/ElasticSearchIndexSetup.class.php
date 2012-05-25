<?php

class ElasticSearchIndexSetup implements IDatabaseSetup
{
    /**
     * @var ElasticSearchDatabase
     */
    protected $database;

    public function __construct($connectionName)
    {
        $this->database = AgaviContext::getInstance()->getDatabaseManager()->getDatabase(
            $connectionName,
            ShofiFinder::getElasticSearchDatabaseName() //@todo legacy support, find-refactor-remove ^^
        );
    }

    public function setup($tearDownFirst = FALSE)
    {
        // Delete before the index to prevent a current river from crashing
        if (TRUE === $tearDownFirst)
        {
            $this->deleteRiver();
        }

        $indexDef = $this->database->getParameter('index', array());
        $indexName = isset($indexDef['name']) ? $indexDef['name'] : NULL;
        $this->createIndex($tearDownFirst);

        $types = isset($indexDef['types']) ? $indexDef['types'] : array();
        foreach ($types as $type)
        {
            $this->createMappig($type);
        }
        
        foreach ($this->database->getParameter('rivers', array()) as $name => $params)
        {
            $this->createRiver($name, $params);
        }
    }

    public function tearDown()
    {
        foreach ($this->database->getParameter('rivers', array()) as $name => $params)
        {
            $this->deleteRiver($name);
        }
        $this->database->getResource()->delete();
    }

    protected function createIndex($tearDownFirst)
    {
        // @todo Make this stuff configurable, hence read/parse definition from an *.index.json
        $this->database->getResource()->create(array(
            'number_of_shards' => 2,
            'number_of_replicas' => 1,
            'analysis' => array(
                'analyzer' => array(
                    'default' => array(
                        'type' => 'custom',
                        'tokenizer' => 'icu_tokenizer',
                        'filter' => array('lowercase', 'snowball', 'icu_folding')
                    ),
                    'searchAnalyzer' => array(
                        'type' => 'custom',
                        'tokenizer' => 'icu_tokenizer',
                        'filter' => array('lowercase', 'snowball', 'icu_folding')
                    ),
                    'containsText' => array(
                        'type' => 'custom',
                        "tokenizer" => "whitespace",
                        "filter" => array('lowercase', "snowball", "icu_folding", "autocomplete")
                    )
                ),
                'filter' => array(
                    'snowball' => array(
                        'type' => 'snowball',
                        'language' => 'German2'
                    ),
                    'autocomplete' => array(
                        'type' => 'edgeNGram',
                        'min_gram' => 1,
                        'max_gram' => 50,
                        'side' => 'front'
                    )
                )
            )
        ), $tearDownFirst);
    }

    protected function createMappig($type)
    {

        $setupDir = $this->database->getParameter('index[setup_dir]', dirname(__FILE__));
        $mappingFile = sprintf('%1$s/%2$s.mapping.json', $setupDir, $type);
        $json = file_get_contents($mappingFile);
        $typeSettings = json_decode($json, TRUE);
        $index = $this->database->getResource();
        $elasticaType = $index->getType($type);
        $mapping = new Elastica_Type_Mapping();
        $mapping->setType($elasticaType);
        foreach ($typeSettings as $prop => $value)
        {
            if ('properties' === $prop)
            {
                continue;
            }
            $mapping->setParam($prop, $value);
        }
        $mapping->setProperties($typeSettings['properties']);
        $mapping->send();
    }

    protected function createRiver($name, array $riverParams)
    {
        $setupDir = $this->database->getParameter('index[setup_dir]', dirname(__FILE__));
        $riverPath = sprintf("_river/%s/_meta", $name);
        $riverSettings = json_decode(
            file_get_contents(
                sprintf("%s/%s.json", $setupDir, $riverParams['config'])
            ),
            TRUE
        );
        $riverSettings['couchdb']['db'] = $riverParams['db'];
        $riverSettings['index']['index'] = $this->database->getResource()->getName();
        $this->database->getConnection()->request($riverPath, Elastica_Request::PUT, $riverSettings);
    }

    protected function deleteRiver($name)
    {
        $riverPath = sprintf("_river/%s", $name);
        $this->database->getConnection()->request($riverPath, Elastica_Request::DELETE);
    }
}

?>
