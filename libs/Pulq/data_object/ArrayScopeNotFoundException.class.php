<?php

class ArrayScopeNotFoundException extends Exception
{
    public function __construct($scopeName)
    {
        parent::__construct(sprintf("Couldn't find array scope: %s", $scopeName));
    }
}
