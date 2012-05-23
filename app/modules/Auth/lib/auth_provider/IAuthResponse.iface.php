<?php

/**
 * The IAuthResponse specifies how authentication attempts shall be answered by IAuthProviders.
 *
 * @version         $Id: IAuthResponse.iface.php 992 2012-02-27 23:21:47Z tschmitt $
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
