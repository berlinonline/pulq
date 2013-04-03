<?php

class Default_RebuildIndices_RebuildIndicesSuccessView extends DefaultBaseView
{
    

    /**
     * Handles the Text output type.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>An AgaviExecutionContainer to forward the execution to or</li>
     *                     <li>Any other type will be set as the response content.</li>
     *                   </ul>
     */
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $this->setupText($rd);

        $this->setAttribute('_title', 'RebuildIndices');
    }
}

?>
