<?php

abstract class FreezableDataObject extends BaseDataObject implements IFreezable
{
    protected $frozen = FALSE;
    
    public function freeze()
    {
        $this->frozen = TRUE;
    }

    public function isFrozen()
    {
        return $this->frozen;
    }

    protected function breakWhenFrozen()
    {
        if ($this->isFrozen())
        {
            throw new Exception("The given ListState instance is frozen and may not be modified.");
        }
    }
}

?>
