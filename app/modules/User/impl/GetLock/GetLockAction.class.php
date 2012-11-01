<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package User
 */
class User_GetLockAction extends UserBaseAction
{
	

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
		return 'Success';
	}
}