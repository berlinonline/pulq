<?php

/**
 * Extends AgaviContext to allow lazy session initializing
 *
 * @author Tom Anheyer
 *
 */
class PulqContext extends AgaviContext
{
    protected $delayedUser = NULL;

    /**
     * Retrieve the storage.
     *
     * @return     AgaviStorage The current Storage implementation instance.
     *
     * @author     Tom Anheyer
     * @since      2013-03-29
     */
    public function getStorage()
    {
        if ($this->storage instanceof PulqProxyStorage)
        {
            $this->storage = $this->storage->getRealInstance();
            array_push($this->shutdownSequence, $this->storage);
        }
        return $this->storage;
    }


    /**
     * Retrieve the user.
     *
     * @return     AgaviUser The current User implementation instance.
     *
     * @author     Tom Anheyer
     * @since      2013-03-29
     */
    public function getUser()
    {
        if ($this->user instanceof PulqProxyUser)
        {
            $this->user = $this->user->getRealInstance();
            array_push($this->shutdownSequence, $this->user);
        }
        return $this->user;
    }
}
