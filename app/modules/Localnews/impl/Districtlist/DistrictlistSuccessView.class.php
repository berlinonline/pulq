<?php

class Localnews_Districtlist_DistrictlistSuccessView extends ProjectLocalnewsBaseView
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
		$this->setupHtml($rd, 'slot');

        $districtService = new LocalnewsDistrictService();

        $this->setAttribute('districts', $districtService->getDistricts());

        $this->setAttribute('menuId', uniqid());
	}

}
