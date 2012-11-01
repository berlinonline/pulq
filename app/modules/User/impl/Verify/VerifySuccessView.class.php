<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package User
 */
class User_Verify_VerifySuccessView extends UserBaseView
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

		$this->setAttribute('_title', 'Verify');
	}
}
