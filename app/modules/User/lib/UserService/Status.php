<?php


/**
  * @class UserService_Status
  *
  * Simple Container for Status messages
  *
  *
  * @package
  * @subpackage
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */

class UserService_Status
{
    public $ok;
    public $code;
    public $message;
    public $parameters;

    public static function create ($ok, $code, $message, $parameters = array())
    {
        $status = new UserService_Status();
        $status->ok = $ok;
        $status->code = intval($code);
        $status->message = $message;
        $status->parameters = $parameters;
        return $status;
    }

    /**
      * Return as Array
      *
      * @return array
      */
    public function toArray ()
    {
        return array(
            'ok' => $this->ok ? TRUE : FALSE,
            'code' => intval($this->code),
            'message' => $this->message,
            'parameters' => is_array($this->parameters) ? $this->parameters : array($this->parameters),
        );
    }

}


?>