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
        $this->setAttribute('districts', $this->getDistricts());
	}

    /**
     * Generates some dummy data as long as there's no working model layer.
     */
    protected function getDistricts()
    {
        return array(
            "Charlottenburg - Wilmersdorf",
            "Friedrichshain - Kreuzberg",
            "Lichtenberg",
            "Marzahn - Hellersdorf",
            "Mitte",
            "Neukölln",
            "Pankow",
            "Reinickendorf",
            "Spandau",
            "Steglitz - Zehlendorf",
            "Tempelhof - Schöneberg",
            "Treptow - Köpenick",
        );
    }
}
