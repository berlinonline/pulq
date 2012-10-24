<?php

class PulqDataObjectSet extends PulqSet
{
    protected $totalCount = 0;

    public function getTotalCount()
    {
        return $this->totalCount;
    }

    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
    }

    public function toArrays($scopeName = null)
    {
        $arrays = array();

        foreach($this as $element)
        {
            $arrays[] = $element->toArray($scopeName);
        }

        return $arrays;
    }
}
