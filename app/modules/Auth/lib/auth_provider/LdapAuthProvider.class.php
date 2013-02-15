<?php

/**
 * The LdapAuthProvider provides authentication to the configured ldap server.
 * It's settings are configured with a 'ldap.' prefix inside the app's settings.xml.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Auth
 * @subpackage      AuthProvider
 */
class LdapAuthProvider extends BaseAuthProvider
{
    /**
     * Holds our LDAP link_identifier resource.
     *
     * @var         resource
     */
    private $ldap;

    public function getTypeIdentifier()
    {
        return 'ldap';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function authenticate($username, $password, $options = array()) // @codingStandardsIgnoreEnd
    {
        $errors = $this->ldapConnect();
        if (! empty($errors))
        {
            return new AuthResponse(
                AuthResponse::STATE_ERROR,
                "ldap connection failed",
                array(),
                $errors
            );
        }

        $errors = $this->ldapLogin($username, $password);
        if (! empty($errors))
        {
            return new AuthResponse(
                AuthResponse::STATE_UNAUTHORIZED,
                "ldap authenticaton failed",
                array(),
                $errors
            );
        }

        if (AgaviConfig::has("ldap.group_required"))
        {
            $errors = $this->verifyGroupMembership($username);
            if (! empty($errors))
            {
                return new AuthResponse(
                    AuthResponse::STATE_UNAUTHORIZED,
                    'ldap authenticaton success',
                    array(),
                    $errors
                );
            }
        }

        $attributes = array();
        try
        {
            $attributes = $this->getUserAttributes($username);
        }
        catch(AgaviSecurityException $e)
        {
            $errors[] = $e->__toString();
            return new AuthResponse(
                AuthResponse::STATE_ERROR,
                'failed fetching attributes',
                array(),
                $errors
            );
        }
        return new AuthResponse(
            AuthResponse::STATE_AUTHORIZED,
            "ldap authenticaton success",
            $attributes
        );
    }

    private function ldapConnect()
    {
        $this->checkLdapConfig();
        $this->ldap = ldap_connect(AgaviConfig::get("ldap.host"), AgaviConfig::get("ldap.port", 389));
        $errors = array();
        if (! $this->ldap)
        {
            // hmm, can't connect to the ldap server.
            $errors[] = "Can not connect to LDAP Server: " . AgaviConfig::get("ldap.host");
        }
        else
        {
            ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, AgaviConfig::get("ldap.protocol", 3));
        }
        return $errors;
    }

    private function ldapLogin($username, $password)
    {
        $errors = array();
        $bindRdn = sprintf(
            "%s=%s,%s",
            AgaviConfig::get("ldap.user_search", "uid"),
            $this->getLdapEscapedString($username),
            AgaviConfig::get("ldap.base_user")
        );
        // @codingStandardsIgnoreStart
        if (! @ldap_bind($this->ldap, $bindRdn, $password)) // @codingStandardsIgnoreEnd
        {
            if (0x31 == ldap_errno($this->ldap))
            {
                $errors[] = sprintf(
                    'Failed authentication attempt for username %1$s, username/password missmatch (%2$s)',
                    $username, ldap_error($this->ldap)
                );
            }
            else
            {
                // ... or due to some other error :(
                $errors[] = AgaviConfig::get("ldap.host") . ': LDAP error: ' . ldap_error($this->ldap);
            }
        }
        return $errors;
    }

    private function verifyGroupMembership($username)
    {
        $errors = array();
        $bindRdn = sprintf(
            "%s=%s,%s",
            AgaviConfig::get("ldap.user_search", "uid"),
            $this->getLdapEscapedString($username),
            AgaviConfig::get("ldap.base_user")
        );
        $uid = $this->getLdapAttribute($username, "uid");
        $ldapDn = sprintf(
            "%s=%s,%s",
            AgaviConfig::get("ldap.group_search"),
            AgaviConfig::get("ldap.group_required"),
            AgaviConfig::get("ldap.base_group")
        );
        $filter = sprintf(
            "(& (objectClass=%s) (%s=%s))",
            AgaviConfig::get("ldap.group_object_class", "posixGroup"),
            AgaviConfig::get("ldap.group_member_attr", "memberUid"),
            $this->getLdapEscapedString(
                AgaviConfig::get("ldap.group_member_attr_is_dn", FALSE) ? $bindRdn : $uid
            )
        );

        $entry = ldap_read($this->ldap, $ldapDn, $filter);
        if (! $entry)
        {
            $errors[] = ldap_error($this->ldap);
        }
        else
        {
            $info = ldap_get_entries($this->ldap, $entry);
            if (! $info || 0 == $info["count"])
            {
                $errors[] = "The given ldap account is not member of a required group.";
            }
        }
        return $errors;
    }

    private function checkLdapConfig()
    {
        $missing = array();

        static $ldap_settings = array(
            "host",
            "base",
            "base_user",
            "base_group",
            "user_search",
            "group_search",
            "user_name_attr",
            "user_email_attr",
            "group_object_class",
            "group_member_attr",
            "group_name_attr"
        );

        foreach ($ldap_settings as $setting)
        {
            if (! AgaviConfig::has("ldap." . $setting))
            {
                $missing[] = "ldap." . $setting;
            }
        }

        if (!empty($missing))
        {
            throw new AgaviConfigurationException("Missing LDAP settings: " . join(", ", $missing));
        }
    }

    /**
     * Return the the ldap attribute value for the given
     * user and attribute name or FALSE if can't be resolved.
     *
     * @param       string $username
     * @param       string $attribute
     *
     * @return      mixed
     *
     * @uses        Auth_LoginAction::getLdapEscapedString()
     */
    private function getLdapAttribute($username, $attribute)
    {
        $ldapDn = sprintf(
            "%s=%s,%s",
            AgaviConfig::get("ldap.user_search", "uid"),
            $this->getLdapEscapedString($username),
            AgaviConfig::get("ldap.base_user")
        );
        $filter = "(objectClass=*)"; // @todo move to constant
        // @codingStandardsIgnoreStart
        $entry = @ldap_read($this->ldap, $ldapDn, $filter, array($attribute));
        if ($entry)
        {
            $info = @ldap_get_entries($this->ldap, $entry); // @codingStandardsIgnoreEnd
            return empty($info[0][$attribute][0]) ? FALSE : $info[0][$attribute][0];
        }
        return FALSE;
    }

    /**
     * Returns a string which has the chars *, (, ), \ & NUL escaped to LDAP compliant
     * syntax as per RFC 2254.
     * Thanks and credit to Iain Colledge for the research and function.
     *
     * @param       string $string
     *
     * @return      string
     */
    private function getLdapEscapedString($string)
    {
        // Make the string LDAP compliant by escaping *, (, ) , \ & NUL
        return str_replace(
            array("*", "(", ")", "\\", "\x00"), //replace this
            array("\\2a", "\\28", "\\29", "\\5c", "\\00"), //with this
            $string //in this
        );
    }

    protected function getUserAttributes($username)
    {
        // Setup default attributes.
        $userAttributes = array(
            'login' => $username,
            'name' => $this->getLdapAttribute($username, AgaviConfig::get("ldap.user_name_attr", "cn")),
            'email' => $this->getLdapAttribute($username, AgaviConfig::get("ldap.user_email_attr", "mail")),
            'ldap_groups' => array()
        );

        // Then find groups of user.
        $distinguishedName =
            AgaviConfig::get("ldap.group_member_attr_is_dn")
                ? sprintf("%s=%s,%s", AgaviConfig::get("ldap.user_search", "uid"),
                    $this->getLdapEscapedString($username), AgaviConfig::get("ldap.base_user"))
                : $this->getLdapAttribute($username, "uid");
        $filter = sprintf(
            "(& (objectClass=%s) (%s=%s))",
            AgaviConfig::get("ldap.group_object_class", "posixGroup"),
            AgaviConfig::get("ldap.group_member_attr", "memberUid"),
            $this->getLdapEscapedString($distinguishedName)
        );
        $ldapEntry = ldap_search(
            $this->ldap, AgaviConfig::get("ldap.base_group"),
            $filter,
            array(AgaviConfig::get("ldap.group_name_attr"))
        );
        if (! $ldapEntry)
        {
            throw new AgaviSecurityException(ldap_error($this->ldap));
        }

        $entryInfo = ldap_get_entries($this->ldap, $ldapEntry);
        if (empty($entryInfo['count']))
        {
            return $userAttributes;
        }

        foreach ($entryInfo as $val)
        {
            $ldapGroupAttr = AgaviConfig::get("ldap.group_name_attr");
            if (! empty($val[$ldapGroupAttr][0]))
            {
                $userAttributes['ldap_groups'][] = $val[$ldapGroupAttr][0];
            }
        }
        if (! empty($userAttributes['ldap_groups']))
        {
            $userAttributes['external_roles'] = $userAttributes['ldap_groups'];
        }
        return $userAttributes;
    }
}

?>
