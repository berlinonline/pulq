<?php


/**
  * @class UserService_Group
  *
  *
  * @package UserService
  * @subpackage Core
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
      * @version $id$
  */

class UserService_Group implements UserService_Group_Interface
{


    /**
      * DB-Representation
      */
    public $group_id;
    protected $groupname;
    protected $applicationid;
    protected $comment;

    public function __construct($groupname, $applicationId = 0)
    {
        if (NULL !== $groupname)
        {
            $this->setName($groupname);
        }
        if (NULL !== $applicationId)
        {
            $this->setApplicationId($applicationId);
        }
    }

    /**
      * Return group informations as array
      *
      * @return array
      */
    public function toArray ()
    {
        return array (
            'group_id' => $this->group_id,
            'groupname' => $this->groupname,
            'applicationid' => $this->applicationid,
            'comment' => $this->comment,
        );
    }



    /**
      * Create a group
      *
      * @param String $groupname
      * @param String $applicationId
      *
      * @return UserService_Group_Interface
      */
    static public function create ($groupname, $applicationId = 0)
    {
        $group = new UserService_Group($groupname, $applicationId);
        return $group;
    }


    /**
      * Return name
      *
      * @return String
      */
    public function getName ()
    {
        return $this->groupname;
    }

    /**
      * Set name
      *
      * @param String $groupname
      *
      * @return UserService_Group_Interface
      * @throws Exception if name is too long or not existent
      */
    public function setName ($groupname)
    {
        if (strlen($groupname) > 255)
        {
            throw Exception("Groupname '$groupname' is too long");
        }
        if (!$groupname)
        {
            throw Exception("Groupname '$groupname' is too short");
        }
        $this->groupname = $groupname;
        return $this;
    }


    /**
      * Check if the Group is global
      *
      * @return TRUE or FALSE
      */
    public function isGlobal ()
    {
        //0 leads to empty string using mysql, so we do a simple test, not "=== 0"
        return !$this->applicationid ? TRUE : FALSE;
    }

    /**
      * Return ApplicationId
      * If ID is "0", the group is global
      *
      * @return String
      */
    public function getApplicationId ()
    {
        if (!$this->applicationid)
        {
            // DB-representation is string, we do not always get a Int 0 back
            return 0;
        }
        return $this->applicationid;
    }

    /**
      * Set ApplicationId
      *
      * @param String $applicationId
      *
      * @return UserService_Group_Interface
      * @throws Exception if name is too long
      */
    public function setApplicationId ($applicationId)
    {
        if (strlen($applicationId) > 255)
        {
            throw Exception("ApplicationId '$applicationId' is too long");
        }
        if ($applicationId === '')
        {
            throw Exception("ApplicationId '' is too short");
        }
        $this->applicationid = $applicationId;
        return $this;
    }

    /**
      * Return comment
      *
      * @return String
      */
    public function getComment ()
    {
        return $this->comment;
    }

    /**
      * Set real name
      *
      * @param String $comment
      *
      * @return UserService_User_Interface
      * @throws Exception if comment is too long
      */
    public function setComment ($comment)
    {
        if (strlen($comment) > 65536)
        {
            throw Exception("Comment is too long");
        }
        $this->comment = $comment;
        return $this;
    }


    /**
      * Compare group with another group
      *
      * @param UserService_Group_Interface
      *
      * @return TRUE or FALSE
      */
    public function isCopyOf (UserService_Group_Interface $group)
    {
        if (
            $this->getName() === $group->getName()
            && $this->getApplicationId() === $group->getApplicationId()
        )
        {
            return TRUE;
        }
        return FALSE;
    }

}


?>