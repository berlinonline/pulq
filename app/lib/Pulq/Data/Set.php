<?php

namespace Pulq\Data;

class Set implements Countable, ArrayAccess, Iterator
{
    private $elements = array();
    private $position = 0;

    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    public function getelements()
    {
        return $this->elements;
    }

    public function count()
    {
        return count($this->elements);
    }

    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->elements[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->elements[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        array_splice($this->elements, $offset, 1);
    }

    public function current()
    {
        if ($this->valid())
        {
            return $this->elements[$this->position];
        }
        else
        {
            return false;
        }
    }

    public function key()
    {
        return key($this->elements);
    }

    public function next()
    {
        $newPos = $this->position + 1;

        if ($this->offsetExists($newPos))
        {
            $retVal = $this->elements[$newPos];
        }
        else
        {
            $retVal = false;
        }
        
        $this->position = $newPos;

        return $retVal;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return $this->offsetExists($this->position);
    }
}

