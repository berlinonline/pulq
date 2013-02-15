<?php

/**
 * The IAuthResponse specifies how authentication attempts shall be answered by IAuthProviders.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Auth
 * @subpackage      AuthProvider
 */
interface IAuthResponse
{
    public function getMessage();

    public function getErrors();

    public function getState();

    public function getAttributes();

    public function getAttribute($name, $default = NULL);
}

?>
