<?php

/**
 * The Auth_LogoutAction class provides standard logout functionality and ends any current session.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <Tom.Anheyer@BerlinOnline.de>
 * @package         Auth
 * @subpackage      Mvc
 */
class Auth_LogoutAction extends AuthBaseAction 
{
    /**
     * This Action does not yet serve any Request methods.
     * When a request comes in and this Action is used, execution will be skipped
     * and the View returned by getDefaultViewName() will be used.
     *
     * If an Action has an execute() method, this means it serves all methods.
     * Alternatively, you can implement executeRead() and executeWrite() methods,
     * because "read" and "write" are the default names for Web Request methods.
     * Other request methods may be explicitely served via execcuteReqmethname().
     *
     * Keep in mind that if an Action serves a Request method, validation will be
     * performed prior to execution.
     *
     * Usually, for example for an AddProduct form, your Action should only be run
     * when a POST request comes in, which is mapped to the "write" method by
     * default. Therefor, you'd only implement executeWrite() and put the logic to
     * add the new product to the database there, while for GET (o.e. "read")
     * requests, execution would be skipped, and the View name would be determined
     * using getDefaultViewName().
     *
     * We strongly recommend to prefer specific executeWhatever() methods over the
     * "catchall" execute().
     *
     * Besides execute() and execute*(), there are other methods that might either
     * be generic or specific to a request method. These are:
     * registerValidators() and register*Validators()
     * validate() and validate*()
     * handleError() and handle*Error()
     *
     * The execution of these methods is not dependent on the respective specific
     * execute*() being present, e.g. for a "write" Request, validateWrite() will
     * be run even if there is no executeWrite() method.
     */
//	public function execute(AgaviParameterHolder $parameters)
//	{
//		return 'Success';
//	}
    
    /**
     * Delegates to our executeWrite method.
     * @internal    Do we want this? Logging out per get request?
     *
     * @param       AgaviParameterHolder $parameters
     * 
     * @return      string The name of the view to execute.
     */
    public function executeRead(AgaviParameterHolder $parameters) 
    {
        return $this->executeWrite($parameters);
    }
    
    /**
     * Logout the current user and end his session.
     *
     * @param       AgaviParameterHolder $parameters
     * 
     * @return      string The name of the view to execute.
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeWrite(AgaviParameterHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->getContext()->getUser()->clearAttributes();
        $this->getContext()->getUser()->setAuthenticated(FALSE);
        
        return 'Success';
    }
    
    /**
     * Return whether this action requires authentication
     * before execution.
     * 
     * @return      boolean
     */
    public function isSecure() 
    {
        return FALSE;
    }
}

?>