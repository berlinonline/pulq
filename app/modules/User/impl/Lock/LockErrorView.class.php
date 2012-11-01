<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id: LockErrorView.class.php 21 2012-03-08 13:51:57Z mfischer $
 * @package User
 */
class User_Lock_LockErrorView extends UserBaseView
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

		$this->setAttribute('_title', 'GetLock');
	}
}
