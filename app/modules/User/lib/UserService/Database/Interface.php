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

interface UserService_Database_Interface
{

    /**
      * Test if a user exists
      *
      * @param String $user_id
      *
      * @return TRUE if user exists, FALSE if not
      * @throws Exception on failure
      */
    public function existsId ($user_id);

    /**
      * Test if a user exists
      *
      * @param String $loginname
      *
      * @return TRUE if user exists, FALSE if not
      */
    public function existsLogin ($loginname);

    /**
      * Fetch a user by his ID
      *
      * @param Int $user_id
      *
      * @return UserService_User_Interface
      * @throws Exception on failure
      */
    public function fetchUserById ($user_id);

    /**
      * Fetch a user by his loginname
      *
      * @param String $loginname
      *
      * @return UserService_User_Interface
      * @throws Exception on failure
      */
    public function fetchUserByLoginname ($loginname);

    /**
      * Fetch a user by his email-address
      *
      * @param String $address
      *
      * @return UserService_User_Interface
      * @throws Exception on failure
      */
    public function fetchUserByMail ($address);

    /**
      * Fetch all users
      *
      * @return array of type UserService_User_Interface
      * @throws Exception on failure
      */
    public function fetchUsers ();

    /**
      * Fetch all groups for a user
      *
      * @param UserService_User_Interface $user
      *
      * @return array of type UserService_Group_Interface
      * @throws Exception on failure
      */
    public function fetchUserGroups (UserService_User_Interface $user);

    /**
      * Create/Replace a user in der DB using the primary key "loginname"
      *
      * @param UserService_User_Interface $user
      *
      * @return UserService_User_Interface with updated properties
      * @throws Exception on failure
      */
    public function replaceUser (UserService_User_Interface $user);


    /**
      * Delete a user in der DB using the primary key "loginname"
      *
      * @param UserService_User_Interface $user
      *
      * @return TRUE on success, FALSE if not
      */
    public function deleteUser (UserService_User_Interface $user);


    /**
      * List all application IDs
      *
      * @return array of type string
      * @throws Exception on failure
      */
    public function listApps ();

    /**
      * List all groups without application
      *
      * @return array of type string
      * @throws Exception on failure
      */
    public function listGroups ();

    /**
      * List all groups of a specified application
      *
      * @param String $applicationId
      *
      * @return array of type string
      * @throws Exception on failure
      */
    public function listAppGroups ($applicationId);




}


?>