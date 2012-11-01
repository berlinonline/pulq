<?php

/**
 * The base view from which all User module views inherit.
 */
class UserBaseView extends ProjectBaseView
{

    /**
     * There are only two type of response: UserService_User_Interface or UserService_Status
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     */
    public function executeJson(AgaviRequestDataHolder $rd)
    {
        $user = $this->getAttribute('user');
        if ($user instanceof UserService_User_Interface)
        {
            //Remove password hash on response
            $response = $user->toArray();
            if (isset($response['pass']))
            {
                unset($response['pass']);
            }
        }
        elseif ($user instanceof UserService_Status)
        {
            $code = $user->code;
            if ($code > 400 && $code < 500 && $code !== 404)
            {
                //Compatibility to HTTP 1.0
                $code = 400;
            }

            $this->getResponse()
                ->setHttpStatusCode($code);
            $response = $user->toArray();
        }
        else
        {
            throw new Exception("Invalid Type of Response");
        }
        return json_encode($response);
    }
}

?>