<?php

class Localnews_Index_IndexSuccessView extends ProjectLocalnewsBaseView
{

	/**
	 * Handles the Html output type.
	 *
	 * @parameter  AgaviRequestDataHolder the (validated) request data
	 *
	 * @return     mixed <ul>
	 *                     <li>An AgaviExecutionContainer to forward the execution to or</li>
	 *                     <li>Any other type will be set as the response content.</li>
	 *                   </ul>
	 */
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);

		$this->setAttribute('_title', 'Index');

        $this->getLayer('content')->setSlot('districtlist', $this->createSlotContainer(
            'Localnews',
            'Districtlist',
            array(),
            'html'
        ));
        $this->getLayer('content')->setSlot('newslist', $this->createSlotContainer(
            'Localnews',
            'Newslist',
            array(),
            'html'
        ));
	}
}
