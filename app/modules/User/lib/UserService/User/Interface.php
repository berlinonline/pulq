<?php


/**
  *
  *
  * @package UserService
  * @subpackage Core
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */

interface UserService_User_Interface
{


    /**
      * Create a user
      *
      * @return UserService_User_Interface
      */
    static public function create ();

    /**
      * Check if the user is locked
      *
      * @return TRUE or FALSE
      */
    public function isLocked ();

    /**
      * Get time in seconds, the user is still locked
      *
      * @return Int
      */
    public function getRemainingLockTime ();


    /**
      * Get a format string specifing the end of the lock time
      *
      * @return String
      */
    public function getExpiresTime ();

    /**
      * Lock the user for n seconds
      *
      * @param Int $seconds or "-1" to lock forever
      *
      * @return TRUE or FALSE
      */
    public function createLock ($seconds);


    /**
      * Add a group
      *
      * @param UserService_Group_Interface $group
      *
      * @return UserService_User_Interface
      */
    public function addGroup (UserService_Group_Interface $group);


    /**
      * Remove a group
      *
      * @param UserService_Group_Interface $group
      *
      * @return UserService_User_Interface
      */
    public function removeGroup (UserService_Group_Interface $group);

    /**
      * Check if the user has a group
      *
      * @param UserService_Group_Interface $group
      *
      * @return TRUE or FALSE
      */
    public function hasGroup (UserService_Group_Interface $group);

    /**
      * Check if the user has a group
      *
      * @return array of type UserService_Group_Interface
      */
    public function getGroups ();

    /**
      * Return user informations as array
      *
      * @return array
      */
    public function toArray ();

    /**
      * Return internal id
      *
      * @return String
      */
    public function getId ();

    /**
      * Return loginname
      *
      * @return String
      */
    public function getLoginname ();

    /**
      * Set Loginname
      *
      * @return String
      */
    public function setLoginname ($loginname);

    /**
      * Return E-Mail
      *
      * @return String
      */
    public function getEmail ();

    /**
      * Check E-Mail address
      *
      * @param String $address
      *
      * @return TRUE or FALSE
      */
    public function checkEmailSyntax ($address);

    /**
      * Set E-Mail address
      *
      * @param String $address
      *
      * @return UserService_User_Interface
      * @throws Exception if address is not valid
      */
    public function setEmail ($address);


    /**
      * Check if a password matches the security requirements
      *
      * @param String $password
      *
      * @return TRUE or FALSE
      */
    public function checkPasswordSecurity ($password);

    /**
      * Set password and stores it encrypted
      *
      * @param String $password
      *
      * @return UserService_User_Interface
      * @throws Exception if password does not match security requirements
      */
    public function setPassword ($password);


    /**
      * Verify password against stored password
      *
      * @param String $password
      *
      * @returns TRUE or FALSE
      */
    public function verifyPassword ($password);


    /**
      * Return real name
      *
      * @return String
      */
    public function getName ();

    /**
      * Set real name
      *
      * @param String $name
      *
      * @return UserService_User_Interface
      * @throws Exception if name is too long
      */
    public function setName ($name);

    /**
      * Return comment
      *
      * @return String
      */
    public function getComment ();

    /**
      * Set comment
      *
      * @param String $comment
      *
      * @return UserService_User_Interface
      * @throws Exception if comment is too long
      */
    public function setComment ($comment);


}


?>