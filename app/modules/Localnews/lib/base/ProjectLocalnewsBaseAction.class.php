<?php

/**
 * The base action from which all Localnews module actions inherit.
 */
class ProjectLocalnewsBaseAction extends ProjectBaseAction
{
    public function isSecure()
    {
        return false;
    }

}
