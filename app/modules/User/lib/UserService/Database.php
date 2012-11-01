<?php


/**
  * @class UserService_Database
  *
  *
  * @package UserService
  * @subpackage Core
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */

class UserService_Database implements UserService_Database_Interface
{

    protected $pdo;

    protected $ownTransactions = 0;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    /**
      * Save beginTransaction for PDO
      *
      */
    protected function beginTransaction ()
    {
        $this->ownTransactions += 1;
        if (!$this->pdo->inTransaction())
        {
            $this->pdo->beginTransaction();
        }
    }


    /**
      * Save commit transactions for PDO
      *
      */
    public function commit ()
    {
        $this->ownTransactions -= 1;
        if (0 === $this->ownTransactions)
        {
            $this->pdo->commit();
        }
    }



    /**
      * Return as SQL-Error-Message
      *
      * @return String
      */
    protected function getErrorMessage()
    {
        $error = $this->pdo->errorInfo();
        return $error[2];
    }

    /**
      * Test if a user exists
      *
      * @param String $loginname
      *
      * @return TRUE if user exists, FALSE if not
      * @throws Exception on failure
      */
    public function existsId ($user_id)
    {
        $sql = UserService_SQL::select()
            ->from('users')
            ->andWhere('user_id', $user_id)
            ->limit(1)
        ;
        $state = $this->pdo->query($sql->getQuery());
        if (!$state)
        {
            throw new Exception('Could not check for user with ID '.$user_id.':'.$this->getErrorMessage().' -> '.$sql->getQuery());
        }
        return $state->rowCount() == 1 ? TRUE : FALSE;
    }

    /**
      * Test if a user exists
      *
      * @param String $loginname
      *
      * @return TRUE if user exists, FALSE if not
      * @throws Exception on failure
      */
    public function existsLogin ($loginname)
    {
        $sql = UserService_SQL::select()
            ->from('users')
            ->andWhere('loginname', $loginname)
            ->limit(1)
        ;
        $state = $this->pdo->query($sql->getQuery());
        if (!$state)
        {
            throw new Exception('Could not check for user '.$loginname.':'.$this->getErrorMessage().' -> '.$sql->getQuery());
        }
        return $state->rowCount() == 1 ? TRUE : FALSE;
    }

    /**
      * Fetch a user by his ID
      *
      * @param Int $id
      *
      * @return UserService_User_Interface
      * @throws Exception on failure
      */
    public function fetchUserById ($id)
    {
        $sql = UserService_SQL::select()
            ->from('users')
            ->andWhere('user_id', $id)
            ->limit(1)
        ;
        $state = $this->pdo->query($sql->getQuery());
        if (!$state)
        {
            throw new Exception('Could not fetch user #'.$id.':'.$this->getErrorMessage().' -> '.$sql->getQuery());
        }
        $user = $state->fetchObject('UserService_User');
        if (!$user instanceof UserService_User)
        {
            throw new UserService_Exception_Found('User #'.$id.' does not exists');
        }
        $user = $this->fetchUserGroupsFor($user);
        return $user;
    }


    /**
      * Fetch a user by his loginname
      *
      * @param String $loginname
      *
      * @return UserService_User_Interface
      * @throws Exception on failure
      */
    public function fetchUserByLoginname ($loginname)
    {
        $sql = UserService_SQL::select()
            ->from('users')
            ->andWhere('loginname', $loginname)
            ->limit(1)
        ;
        $state = $this->pdo->query($sql->getQuery());
        if (!$state)
        {
            throw new Exception('Could not fetch user '.$loginname.':'.$this->getErrorMessage().' -> '.$sql->getQuery());
        }
        $user = $state->fetchObject('UserService_User');
        if (!$user instanceof UserService_User)
        {
            throw new UserService_Exception_Found('User "'.$loginname.'" does not exists');
        }
        $user = $this->fetchUserGroupsFor($user);
        return $user;
    }




    /**
      * Fetch a user by his email-address
      *
      * @param String $address
      *
      * @return UserService_User_Interface
      * @throws Exception on failure
      */
    public function fetchUserByMail ($address)
    {
        $sql = UserService_SQL::select()
            ->from('users')
            ->andWhere('email', $address)
            ->limit(1)
        ;
        $state = $this->pdo->query($sql->getQuery());
        if (!$state)
        {
            throw new Exception('Could not fetch user by '.$address.':'.$this->getErrorMessage().' -> '.$sql->getQuery());
        }
        $user = $state->fetchObject('UserService_User');
        if (!$user instanceof UserService_User)
        {
            throw new UserService_Exception_Found('User #'.$id.' does not exists');
        }
        $user = $this->fetchUserGroupsFor($user);
        return $user;
    }

    /**
      * Fetch all users
      *
      * @return array of type UserService_User_Interface
      * @throws Exception on failure
      */
    public function fetchUsers ()
    {
        $sql = UserService_SQL::select()
            ->from('users')
        ;
        $state = $this->pdo->query($sql->getQuery());
        if (!$state)
        {
            throw new Exception('Could not fetch users: '.$this->getErrorMessage().' -> '.$sql->getQuery());
        }
        $users = array();
        while ($user = $state->fetchObject('UserService_User'))
        {
            $user = $this->fetchUserGroupsFor($user);
            $users[] = $user;
        }
        return $users;
    }

    /**
      * Fetch all groups for a user
      *
      * @param UserService_User_Interface $user
      *
      * @return array of type UserService_Group_Interface
      * @throws Exception on failure
      */
    public function fetchUserGroups (UserService_User_Interface $user)
    {
        $sql = UserService_SQL::select()
            ->from('groups')
            ->join('xusergroup','group_id')
            ->andWhere('xusergroup.user_id', $user->user_id)
        ;
        $state = $this->pdo->query($sql->getQuery());
        if (!$state)
        {
            throw new Exception('Could not fetch group for user '.$user->getLoginname().': '.$this->getErrorMessage().' -> '.$sql->getQuery());
        }
        $groups = $state->fetchAll(PDO::FETCH_CLASS,'UserService_Group', array(NULL, NULL));
        return $groups;
    }

    /**
      * Fetch all groups for a user and add them to the user
      *
      * @param UserService_User_Interface $user
      *
      * @return UserService_User_Interface
      * @throws Exception on failure
      */
    protected function fetchUserGroupsFor (UserService_User_Interface $user)
    {
        foreach ($this->fetchUserGroups($user) as $group)
        {
            $user->addGroup($group);
        }
        return $user;
    }

    /**
      * Create/Replace a user in der DB using the primary key "loginname"
      *
      * @param UserService_User_Interface $user
      *
      * @return UserService_User_Interface with updated properties
      * @throws Exception on failure
      */
    public function replaceUser (UserService_User_Interface $user)
    {
        $this->beginTransaction();
        $sql = UserService_SQL::replace()->into('users');
        $sql->addValues($user->toArray());
        $status = $this->pdo->exec($sql->getQuery());
        if (!$status)
        {
            $this->pdo->rollBack();
            throw new Exception('Could not replace user '.$user->loginname.':'.$this->getErrorMessage().' -> '.$sql->getQuery());
        }

        if (!$user->user_id)
        {
            // @todo might be corrupt, fetch user instead
            $user_id = $this->pdo->lastInsertId();
            $user->user_id = $user_id;
        }
        // @todo Insert groups

        foreach ($user->getGroups() as $group)
        {
            $this->replaceGroup($user, $group);
        }
        $this->commit();
        return $user;
    }


    /**
      * Replace groups into DB
      */
    protected function replaceGroup(UserService_User_Interface $user, UserService_Group_Interface $group)
    {
        $user_id = $user->getId();
        $sql = UserService_SQL::replace()->into('groups');
        $sql->addValues($group->toArray());
        $status = $this->pdo->exec($sql->getQuery());
        if (!$status)
        {
            $this->pdo->rollBack();
            throw new Exception('Could not replace group '.$group->groupname.':'.$this->getErrorMessage().' -> '.$sql->getQuery());
        }
        $group_id = $this->pdo->lastInsertId();
        $sql = UserService_SQL::replace()->into('xusergroup');
        $sql->addValue('user_id', $user_id);
        $sql->addValue('group_id', $group_id);
        $status = $this->pdo->exec($sql->getQuery());
        if (!$status)
        {
            $this->pdo->rollBack();
            throw new Exception('Could not replace xusergroup for '.$group->groupname.':'.$this->getErrorMessage().' -> '.$sql->getQuery());
        }
    }


    /**
      * Delete a user in der DB using the primary key "loginname"
      *
      * @param UserService_User_Interface $user
      *
      * @return TRUE on success, FALSE if not
      * @throws Exception on failure
      */
    public function deleteUser (UserService_User_Interface $user)
    {
        if (!$user->getId())
        {
            throw new Exception("Cannot delete user, fetch the user from the DB first!");
        }

        $sql = UserService_SQL::delete()->from('xusergroup');
        $sql->andWhere('user_id', $user->getId());
        $status = $this->pdo->exec($sql->getQuery());

        $sql = UserService_SQL::delete()->from('users');
        $sql->andWhere('loginname', $user->getLoginname());
        $status = $this->pdo->exec($sql->getQuery());

        $sql = UserService_SQL::delete()->from('groups')->join('xusergroup', 'group_id');
        $sql->andWhereNull('xusergroup.user_id');
        $status = $this->pdo->exec($sql->getQuery());

        return $status;
    }


    /**
      * List all application IDs
      *
      * @return array of type string
      * @throws Exception on failure
      */
    public function listApps ()
    {
        $sql = UserService_SQL::select()
            ->from('groups')
            ->andWhere('applicationId')
        ;
        $state = $this->pdo->query($sql->getQuery());
        $ids = array();
        while ($group = $state->fetchObject('UserService_Group', array(NULL, NULL)))
        {
            $ids[] = $group->getApplicationId();
        }
        return $ids;
    }

    /**
      * List all groups without application
      *
      * @return array of type string
      * @throws Exception on failure
      */
    public function listGroups ()
    {
        return $this->listAppGroups('');
    }

    /**
      * List all groups of a specified application
      *
      * @param String $applicationId
      *
      * @return array of type string
      * @throws Exception on failure
      */
    public function listAppGroups ($applicationId)
    {
        $sql = UserService_SQL::select()
            ->from('groups')
            ->andWhere('applicationId', $applicationId)
        ;
        $state = $this->pdo->query($sql->getQuery());
        if (!$state)
        {
            throw new Exception('Could not fetch groups: '.$this->getErrorMessage().' -> '.$sql->getQuery());
        }
        $groups = array();
        while ($group = $state->fetchObject('UserService_Group', array(NULL, NULL)))
        {
            $groups[] = $group->getName();
        }
        return $groups;
    }


}


?>
