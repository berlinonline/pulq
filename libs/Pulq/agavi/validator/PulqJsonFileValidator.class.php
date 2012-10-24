<?php

/**
 * @class ProjectJsonValidator
 * Validates request body to be of type json
 *
 * @see        AgaviBaseFileValidator
 *
 * @package    project
 * @subpackage validator
 *
 * @author     <mathias.fischer@berlinonline.de>
 * @copyright  BerlinOnline Stadtportale GmbH & Co. KG
 *
 *
 * @version    $Id: ProjectJsonValidator.class.php 20 2012-03-07 15:10:44Z mfischer $
 */
class PulqJsonFileValidator extends AgaviBaseFileValidator
{
    /**
     * Validates the input
     *
     * @return     bool The file is valid according to given parameters.
     */
    protected function validate()
    {
        if (!parent::validate())
        {
            return FALSE;
        }

        foreach ($this->getArguments() as $argument)
        {
            $file = $this->getData($argument);
            if (!$file instanceof AgaviUploadedFile)
            {
                $this->throwError('argument_wrong_type');
                return FALSE;
            }
            else
            {
                json_decode($file->getContents());
                switch (json_last_error())
                {
                case JSON_ERROR_NONE:
                    return TRUE;
                case JSON_ERROR_DEPTH:
                    $this->throwError('JSON_ERROR_DEPTH');
                    return FALSE;
                case JSON_ERROR_STATE_MISMATCH:
                    $this->throwError('JSON_ERROR_STATE_MISMATCH');
                    return FALSE;
                case JSON_ERROR_CTRL_CHAR:
                    $this->throwError('JSON_ERROR_CTRL_CHAR');
                    return FALSE;
                case JSON_ERROR_SYNTAX:
                    $this->throwError('JSON_ERROR_SYNTAX');
                    return FALSE;
                case JSON_ERROR_UTF8:
                    $this->throwError('JSON_ERROR_UTF8');
                    return FALSE;
                default:
                    $this->throwError('JSON_ERROR_UNKNOW');
                    return FALSE;
                }
            }
        }
        return TRUE;
    }
}

?>