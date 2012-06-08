<?php

class Localnews_ByDistrict_ByDistrictSuccessView extends ProjectLocalnewsBaseView
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

		$this->setAttribute('_title', 'ByDistrict');

        $district = $rd->getParameter('district');

        $this->setAttribute('district', $district);


        $newsService = new LocalnewsService();
        $this->setAttribute('newsitems', $newsService->getNewsByDistrict($district));
	}
}

?>
