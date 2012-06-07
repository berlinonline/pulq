<?php

class DistrictNotFoundException extends Exception
{
    public function __contruct($name)
    {
        parent::__construct(sprintf('Can not find district "%s".', $name));
    }
}
