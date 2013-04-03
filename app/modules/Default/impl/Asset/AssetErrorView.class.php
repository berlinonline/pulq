<?php

class Default_Asset_AssetErrorView extends DefaultBaseView 
{

    public function executeBinary(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->getResponse()->setHttpHeader('Content-Type', 'text/plain');
        $this->getResponse()->setHttpStatusCode('404');
        
        $report = $this->getContainer()->getValidationManager()->getReport();
        $incidents = $report->getIncidents();

        foreach($incidents as $incident)
        {
            foreach ($incident->getErrors() as $error)
            {
                echo $error->getMessage().PHP_EOL;
            }
        }
    }
}

