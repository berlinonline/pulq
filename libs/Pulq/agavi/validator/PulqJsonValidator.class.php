<?php

/**
 * Check for valid JSON argument
 *
 * Parameters:
 *  * assoc - (boolean) matches argument 2 of json_decode()
 *  * keys - expected keys as CSV list
 *  * export - name of export parameter
 *
 * Errors:
 *  * empty
 *  * constant names defined by json_last_error()
 *  * name of key defined by 'keys' parameter
 *
 * @author tay
 * @since 03.07.2012
 *
 */
class JsonValidator extends AgaviValidator
{
    protected function validate()
    {
        $value = $this->getData($this->getArgument());
        if (empty($value))
        {
            $this->throwError('empty');
            return FALSE;
        }

        $json = json_decode($value, $this->getParameter('assoc', TRUE));
        switch (json_last_error())
        {
        case JSON_ERROR_NONE:
            break;
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

        if ($this->hasParameter('keys'))
        {
            foreach (explode(',', $this->getParameter('keys')) as $key)
            {
                if (!array_key_exists($key, $json))
                {
                    $this->throwError($key);
                    return FALSE;
                }
            }
        }

        $this->export($json, $this->getParameter('export', $this->getArgument()));
        return TRUE;
    }
}
