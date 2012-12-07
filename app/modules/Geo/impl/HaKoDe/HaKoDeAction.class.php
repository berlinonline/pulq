<?php

/**
 *
 *
 * @author tay
 * @since 18.04.2012
 *
 */
class Geo_HaKoDeAction extends ProjectGeoBaseAction
{

    /**
     *
     * @var array
     */
    protected $streets = array();
    /**
     *
     * @var array
     */
    protected $potplz = array();


	/**
	 * Handles the Read request method.
	 *
	 * @parameter  AgaviRequestDataHolder the (validated) request data
	 *
	 * @return     mixed <ul>
	 *                     <li>A string containing the view name associated
	 *                     with this action; or</li>
	 *                     <li>An array with two indices: the parent module
	 *                     of the view to be executed and the view to be
	 *                     executed.</li>
	 *                   </ul>^
	 */
	public function executeRead(AgaviRequestDataHolder $rd)
	{
	    $search = new ShofiSearchPeer($rd);

	    $list = $search->findHouse();
	    $this->setAttribute('result', $list);

		return 'Success';
	}

	/**
	 * Handles the Write request method.
	 *
	 * @parameter  AgaviRequestDataHolder the (validated) request data
	 *
	 * @return     mixed <ul>
	 *                     <li>A string containing the view name associated
	 *                     with this action; or</li>
	 *                     <li>An array with two indices: the parent module
	 *                     of the view to be executed and the view to be
	 *                     executed.</li>
	 *                   </ul>^
	 */
	public function executeWrite(AgaviRequestDataHolder $rd)
	{
		return $this->executeRead($rd);
	}


}

?>
