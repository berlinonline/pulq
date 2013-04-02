<?php
/**
 * Helper for delayed/lazy user initialisation
 *
 * @author Tom Anheyedr
 * @since 2013-03-29
 *
 */
class PulqProxyStorage extends AgaviStorage
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
            throw new AgaviConfigurationException('Parameter "class" must specified. This class must extend "AgaviStorage"');
        }
        $this->context = $context;
        $this->parameters = $parameters;
    }


	/**
	 * get the real agavi user instance.
	 *
	 * This is only called once by {@see PulqContext::getUser()}
	 *
	 * @see PulqContext::getUser()
	 * @return AgaviStorage
	 */
	public function getRealInstance()
	{
	    /* @var $instance AgaviStorage */
	    $instance = new $this->parameters['class']();
	    $instance->initialize($this->context, $this->parameters);
	    $instance->startup();
	    return $instance;
	}


	public function startup()
	{
	}

	function read($key)
	{
	    return NULL;
	}

	function remove($key)
	{
	    return NULL;
	}

	function shutdown()
	{
	}

	function write($key, $data)
	{
	}

}