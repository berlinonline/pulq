<?php

class Common_HeaderAction extends CommonBaseAction
{
    const BREADCRUMB_NAMESPACE = 'honeybee.breadcrumbs';

    public function execute(AgaviRequestDataHolder $parameters)
    {
        $user = $this->getContext()->getUser();
        $breadcrumbs = array();
        if ($user->isAuthenticated())
        {
            $breadcrumbs = $user->getAttribute('breadcrumbs', self::BREADCRUMB_NAMESPACE, array());
        }
        $this->setAttribute('breadcrumbs', $breadcrumbs);
        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}

?>