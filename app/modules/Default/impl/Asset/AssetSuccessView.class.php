<?php

class Default_Asset_AssetSuccessView extends DefaultBaseView 
{

    public function executeBinary(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $asset = $parameters->getParameter('asset');

        $this->getResponse()->setContentType($asset->getMime());

        return base64_decode($asset->getData());
    }
}
