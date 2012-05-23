<?php

/**
 * The SimpleAuthProvider provides authentication against xml based account information.
 * The accounts used by te simple auth provider are configured inside the settings.xml.
 *
 * @version         $Id: SimpleAuthProvider.class.php 992 2012-02-27 23:21:47Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Auth
 * @subpackage      AuthProvider
 */
class SimpleAuthProvider extends BaseAuthProvider
{
    private $accounts;

    public function __construct()
    {
        $this->accounts = AgaviConfig::get('core.simple_logins', array());
    }

    public function getTypeIdentifier()
    {
        return 'simple';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function authenticate($username, $password, $options = array()) // @codingStandardsIgnoreEnd
    {
        $errors = array();

        if (isset($this->accounts[$username]) && $this->accounts[$username]['pwd'] === $password)
        {
            return new AuthResponse(
                AuthResponse::STATE_AUTHORIZED,
                "authenticaton success",
                $this->accounts[$username]['attributes']
            );
        }

        return new AuthResponse(
            AuthResponse::STATE_UNAUTHORIZED,
            "authentication failed",
            array(),
            $errors
        );
    }
}

?>
