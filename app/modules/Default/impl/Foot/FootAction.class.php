<?php

class Default_FootAction extends DefaultBaseAction
{


    /**
     * Whether or not this action is "simple", i.e. doesn't use validation etc.
     *
     * @return     bool true, if this action should act in simple mode, or false.
     *
     */
    public function isSimple()
    {
        return TRUE;
    }


    /**
     * Returns the default view if the action does not serve the request
     * method used.
     *
     * @return     mixed <ul>
     *                     <li>A string containing the view name associated
     *                     with this action; or</li>
     *                     <li>An array with two indices: the parent module
     *                     of the view to be executed and the view to be
     *                     executed.</li>
     *                   </ul>
     */
    public function getDefaultViewName()
    {
        return 'Success';
    }
}

?>