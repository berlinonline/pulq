<?php

class Route implements IObjectAllowedInScopedArray
{
    protected $routeName;
    protected $params;

    public function __construct($routeName, array $params)
    {
        $this->routeName = $routeName;
        $this->params = $params;
    }

    public function gen()
    {
        $args = array_chunk(func_get_args(), 2);
        $additionalParams = array();

        foreach ($args as $arg)
        {
            if (count($arg) === 2)
            {
                $additionalParams[$arg[0]] = $arg[1];
            }
        }

        return AgaviContext::getInstance()->getRouting()->gen(
            $this->routeName,
            array_merge($this->params, $additionalParams)
        );
    }

    public function __toString()
    {
        return $this->gen();
    }
}
