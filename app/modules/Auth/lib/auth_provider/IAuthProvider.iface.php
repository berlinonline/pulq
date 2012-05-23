<?php

/**
 * The IAuthProvider specifies how authentication shall be exposed to consuming components
 * inside the Auth module.
 *
 * @version         $Id: IAuthProvider.iface.php 992 2012-02-27 23:21:47Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Auth
 * @subpackage      AuthProvider
 */
interface IAuthProvider
{
    public function getTypeIdentifier();

    public function authenticate($username, $password, $options = array());
}

?>
