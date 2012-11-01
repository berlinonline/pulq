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

interface UserService_Group_Interface
{


    /**
      * Create a group
      *
      * @param String $groupname
      * @param String $applicationId
      *
      * @return UserService_Group_Interface
      */
    static public function create ($groupname, $applicationId = 0);


    /**
      * Return group informations as array
      *
      * @return array
      */
    public function toArray ();


    /**
      * Return name
      *
      * @return String
      */
    public function getName ();

    /**
      * Set name
      *
      * @param String $groupname
      *
      * @return UserService_Group_Interface
      * @throws Exception if name is too long
      */
    public function setName ($groupname);


    /**
      * Check if the Group is global
      *
      * @return TRUE or FALSE
      */
    public function isGlobal ();

    /**
      * Return ApplicationId
      * If ID is "0", the group is global
      *
      * @return String
      */
    public function getApplicationId ();

    /**
      * Set ApplicationId
      *
      * @param String $applicationId
      *
      * @return UserService_Group_Interface
      * @throws Exception if name is too long
      */
    public function setApplicationId ($applicationId);

    /**
      * Return comment
      *
      * @return String
      */
    public function getComment ();

    /**
      * Set real name
      *
      * @param String $comment
      *
      * @return UserService_User_Interface
      * @throws Exception if comment is too long
      */
    public function setComment ($comment);

    /**
      * Compare group with another group
      *
      * @param UserService_Group_Interface
      *
      * @return TRUE or FALSE
      */
    public function isCopyOf (UserService_Group_Interface $group);



}


?>