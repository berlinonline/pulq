<?php
/**
 * Helper for delayed/lazy user initialisation
 *
 * @author Tom Anheyedr
 * @since 2013-03-29
 *
 */
class PulqProxyUser implements AgaviISecurityUser
{
    /**
	 * @var AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * parameters for the real user class
	 *
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * memorize parameters for later class init
	 *
	 * @param AgaviContext $context
	 * @param array $parameters
	 */
    public function initialize(AgaviContext $context, array $parameters = array())
    {
        if (empty($parameters['class']))
        {
            throw new AgaviConfigurationException('Parameter "class" must specified. This class must extend "AgaviUser"');
        }
        $this->context = $context;
        $this->parameters = $parameters;
    }


	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext An AgaviContext instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * get the real agavi user instance.
	 *
	 * This is only called once by {@see PulqContext::getUser()}
	 *
	 * @see PulqContext::getUser()
	 * @return AgaviUser
	 */
	public function getRealInstance()
	{
	    /* @var $instance AgaviUser */
	    $instance = new $this->parameters['class']();
	    $instance->initialize($this->context, $this->parameters);
	    $instance->startup();
	    return $instance;
	}

	public function startup()
	{
	}

	function shutdown()
	{
	}

	/*
	 * Implement interface AgaviISecurityUser to satisfy requirements of AgaviFactoryConfigHandler
	 *
	 * @see AgaviISecurityUser
	 * @see AgaviFactoryConfigHandler
	 */

	public function addCredential($credential)
	{
	}

	public function clearCredentials()
	{
	}

	public function hasCredentials($credential)
	{
	    return FALSE;
	}

	public function isAuthenticated()
	{
	    return FALSE;
	}

	public function removeCredential($credential)
	{
	}

	public function setAuthenticated($authenticated)
	{
	}
}