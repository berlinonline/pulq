<?php

/**
 *
 *
 * @author tay
 * @version $Id: GeoHouseSuccessView.class.php 4991 2012-05-29 07:03:30Z tay $
 * @since 24.05.2012
 *
 */
class Geo_HaKoDe_HaKoDeSuccessView extends ProjectGeoBaseView
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
	    return '<html>'
	    . '<head><title>Geo Suche</title></head>'
	    . '<body>'
	    . '<h1>Geo Suche</h1>'
	    . '<h2>Parameter</h2>'
	    . '<pre>'.htmlspecialchars(print_r($rd->getParameters(),1)).'</pre>'
	    . '<h2>Ergebnisse</h2>'
	    . '<pre>'.htmlspecialchars(print_r($this->getAttribute('result'),1)).'</pre>'
	    . '</body>'
	    . '</html>';
	}

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
	    return json_encode($this->getAttribute('result'));
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

		$this->setAttribute('_title', 'GeoHouse');
	}
}

?>