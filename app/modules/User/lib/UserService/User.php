<?php


/**
  * @class UserService_User
  *
  *
  * @package UserService
  * @subpackage Core
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */

class UserService_User implements UserService_User_Interface
{

    const LOGINLENGTH = 100;
    /**
      * DB-Representation
      */
    public $user_id;
    protected $loginname;
    protected $realname;
    protected $email;
    protected $pass;
    protected $locktime;
    protected $comment;


    /**
      * Groups
      */
    protected $groups = array();

    public function __construct()
    {
    }

    /**
      * Create a user
      *
      * @param String $loginname
      *
      * @return UserService_User_Interface
      */
    static public function create ()
    {
        $user = new UserService_User();
        return $user;
    }

    /**
      * Check if the user is locked
      *
      * @return TRUE or FALSE
      */
    public function isLocked ()
    {
        return $this->getRemainingLockTime() !== 0 ? TRUE : FALSE;
    }

    /**
      * Get time in seconds, the user is still locked
      *
      * @return Int
      */
    public function getRemainingLockTime ()
    {
        if (!$this->locktime)
        {
            return 0;
        }
        if ($this->locktime === -1)
        {
            return -1;
        }
        $seconds = $this->locktime - time();
        return $seconds > 0 ? $seconds : 0;
    }

    /**
      * Get a format string specifing the end of the lock time
      *
      * @return String
      */
    public function getExpiresTime ()
    {
        if ($this->locktime === -1)
        {
            // According to RFC 2616, an expires time should never be greater than one year
            return date('r', time() + ( 60 * 60 * 24 * 365));
        }
        else
        {
            return date('r', $this->locktime);
        }
    }


    /**
      * Lock the user for n seconds
      *
      * @param Int $seconds or "-1" to lock forever
      *
      * @return UserService_User_Interface
      */
    public function createLock ($seconds)
    {
        if ($seconds === -1)
        {
            $this->locktime = -1;
        }
        else
        {
            $seconds = intval($seconds);
            $this->locktime = time() + $seconds;
        }
        return $this;
    }


    /**
      * Add a group
      *
      * @param UserService_Group_Interface $group
      *
      * @return UserService_User_Interface
      */
    public function addGroup (UserService_Group_Interface $group)
    {
        if (FALSE === $this->hasGroup($group))
        {
            $this->groups[] = $group;
        }
        return $this;
    }


    /**
      * Remove a group
      *
      * @param UserService_Group_Interface $group
      *
      * @return UserService_User_Interface
      */
    public function removeGroup (UserService_Group_Interface $group)
    {
        if (FALSE !== $this->hasGroup($group))
        {
            foreach ($this->groups as $key => $comparable)
            {
                if ($group->isCopyOf($comparable))
                {
                    unset($this->groups[$key]);
                }
            }
        }
        return $this;
    }

    /**
      * Check if the user has a group
      *
      * @param UserService_Group_Interface $group
      *
      * @return TRUE or FALSE
      */
    public function hasGroup (UserService_Group_Interface $group)
    {
        foreach ($this->groups as $key => $comparable)
        {
            if ($group->isCopyOf($comparable))
            {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
      * Check if the user has a group
      *
      * @return array of type UserService_Group_Interface
      */
    public function getGroups ()
    {
        return $this->groups;
    }

    /**
      * Return user informations as array
      *
      * @return array
      */
    public function toArray ()
    {
        return array(
            'user_id' => (int) $this->user_id,
            'loginname' => (string) $this->loginname,
            'realname' => (string) $this->realname,
            'email' => (string) $this->email,
            'pass' => (string) $this->pass,
            'locktime' => (string) $this->locktime,
            'comment' => (string) $this->comment,
        );
    }

    /**
      * Set Values using an array
      *
      * @param array $array
      *
      * @return UserService_User_Interface
      */
    public function fromArray (array $array)
    {
        if (isset($array['user_id']))
        {
            $this->user_id = $array['user_id'];
        }
        if (isset($array['loginname']))
        {
            $this->setLoginname($array['loginname']);
        }
        if (isset($array['realname']))
        {
            $this->setName($array['realname']);
        }
        if (isset($array['email']))
        {
            $this->setEmail($array['email']);
        }
        if (isset($array['password']))
        {
            $this->setPassword($array['password']);
        }
        if (isset($array['comment']))
        {
            $this->setComment($array['comment']);
        }
        return $this;
    }

    /**
      * Return loginname
      *
      * @return String
      */
    public function getId ()
    {
        return $this->user_id;
    }

    /**
      * Return loginname
      *
      * @return String
      */
    public function getLoginname ()
    {
        return $this->loginname;
    }

    /**
      * Set loginname
      *
      * @param String $loginname
      *
      * @return UserService_User_Interface
      * @throws Exception if loginname is too long
      */
    public function setLoginname ($loginname)
    {
        if (strlen($loginname) > self::LOGINLENGTH)
        {
            throw new Exception("Loginname '$loginname' is too long");
        }
        if (preg_match('#[^a-zA-Z0-9\._\-]#', $loginname))
        {
            throw new Exception("Loginname '$loginname' contains invalid characters");
        }
        $this->loginname = $loginname;
        return $this;
    }



    /**
      * Return E-Mail
      *
      * @return String
      */
    public function getEmail ()
    {
        return $this->email;
    }

    /**
      * Check E-Mail address
      *
      * @param String $address
      *
      * @return TRUE or FALSE
      */
    public function checkEmailSyntax ($address)
    {
        if (preg_match('/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/', $address))
        {
            return TRUE;
        }
        return FALSE;
    }

    /**
      * Set E-Mail address
      *
      * @param String $address
      *
      * @return UserService_User_Interface
      * @throws Exception if address is not valid
      */
    public function setEmail ($address)
    {
        if (FALSE === $this->checkEmailSyntax($address))
        {
            throw new Exception("Invalid mail address: $address");
        }
        $this->email = $address;
        return $this;
    }


    /**
      * Check if a password matches the security requirements
      *
      * @param String $password
      *
      * @return TRUE or FALSE
      */
    public function checkPasswordSecurity ($password)
    {
        return $this->loginname !== $password ? TRUE : FALSE;
    }

    /**
      * Set password and stores it encrypted
      * Hashing is a combination of type sha1(md5(...))
      * (This allows to migrate older md5-Hashes and helps a little bit against some rainbow tables)
      *
      * @param String $password
      *
      * @return UserService_User_Interface
      * @throws Exception if password does not match security requirements
      */
    public function setPassword ($password)
    {
        if (FALSE === $this->checkPasswordSecurity($password))
        {
            throw new Exception("Invalid password");
        }
        $this->pass = self::hashString($password);
        return $this;
    }


    /**
      * Verify password against stored password
      *
      * @param String $password
      *
      * @return TRUE or FALSE
      */
    public function verifyPassword ($password)
    {
        $salt = self::sha1salt($this->pass);
        return self::hashString($password, $salt) === $this->pass ? TRUE : FALSE;
    }


    /**
      * @param String $string
      * @param String $salt (optional)
      *
      * @return String hashed string 40 chars + salt
      */
    private static function hashString($string, $salt = FALSE)
    {
        if (FALSE === $salt)
        {
            $salt = substr(sha1(rand()), 0, 8);
        }
        return sha1(md5($string).$salt).$salt;
    }

    /**
      * @param String $sha1 hexdecimal encoded sha1 string with a length of 40 chars
      *
      * @return String
      */
    private static function sha1salt($sha1)
    {
        return substr($sha1,40);
    }

    /**
      * Return real name
      *
      * @return String
      */
    public function getName ()
    {
        return $this->realname;
    }

    /**
      * Set real name
      *
      * @param String $name
      *
      * @return UserService_User_Interface
      * @throws Exception if name is too long
      */
    public function setName ($name)
    {
        if (strlen($name) > 65536)
        {
            throw new Exception("Real name is too long");
        }
        $this->realname = $name;
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
      * Set comment
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
            throw new Exception("Comment is too long");
        }
        $this->comment = $comment;
        return $this;
    }


}


?>