<?php

/**
 * The IAuthProvider specifies how authentication shall be exposed to consuming components
 * inside the Auth module.
 *
 * @version         $Id$
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
