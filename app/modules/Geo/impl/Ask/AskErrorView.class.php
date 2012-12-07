<?php

class Geo_Ask_AskErrorView extends ProjectGeoBaseView
{
    

    /**
     * Handles the Json output type.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>An AgaviExecutionContainer to forward the execution to or</li>
     *                     <li>Any other type will be set as the response content.</li>
     *                   </ul>
     */
    public function executeJson(AgaviRequestDataHolder $rd)
    {
        $this->setupJson($rd);

        $this->setAttribute('_title', 'Ask');
    }

    /**
     * Handles the Xml output type.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>An AgaviExecutionContainer to forward the execution to or</li>
     *                     <li>Any other type will be set as the response content.</li>
     *                   </ul>
     */
    public function executeXml(AgaviRequestDataHolder $rd)
    {
        $this->setupXml($rd);

        $this->setAttribute('_title', 'Ask');
    }
}

?>