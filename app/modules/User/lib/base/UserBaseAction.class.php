<?php

/**
 * The base action from which all User module actions inherit.
 */
class UserBaseAction extends ProjectBaseAction
{
    /**
     * (non-PHPdoc)
     * @see ProjectBaseAction::executeRead()
     */
    public function executeRead(AgaviRequestDataHolder $rd)
    {
        $status = UserService_Status::create(FALSE, 404, "GET method not supported", array());
        $this->setAttribute('user', $status);
        return 'Error';
    }

    /**
     *
     *
     * @param AgaviRequestDataHolder $rd
     * @return string
     */
    public function executeWrite(AgaviRequestDataHolder $rd)
    {
        $status = UserService_Status::create(FALSE, 404, "POST method not supported", array());
        $this->setAttribute('user', $status);
        return 'Error';
    }

    /**
     * @return UserService_Database
     */
    protected function getUserService_Database()
    {
        $db = new UserService_Database($this->getContext()->getDatabaseConnection('UserServiceDB'));
        return $db;
    }



    /**
     * Default error handling for all methods
     *
     * @param AgaviRequestDataHolder $parameters
     * @return array (modulename, viewname)
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function handleError(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $container = $this->getContainer();
        $validation_manager = $container->getValidationManager();
        $errors = $validation_manager->getReport()->getErrors();
        $messages = array();
        foreach ($errors as $error)
        {
            $messages[] = $error->getMessage();
        }
        $message = implode(' | ', $messages);
        if (!$message)
        {
            $modul = $container->getModuleName();
            $action = $container->getActionName();
            $message = "Unknown error in $modul::$action";
            throw new Exception($message);
        }
        $status = UserService_Status::create(FALSE, 400, $message);
        $this->setAttribute('user', $status);
        return "Error";
    }

}

?>