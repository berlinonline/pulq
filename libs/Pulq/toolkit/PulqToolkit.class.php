<?php
/**
 *
 *
 * @author tay
 * @since 24.10.2012
 *
 */
class PulqToolkit
{


    /**
     * translate a string to a xml id compatible string
     *
     * The result contains only alpha numeric ASCII chars
     *
     * @param string $str
     * @param string $wordsplit single character to distinct words
     * @return string
     */
    public static function toIdString($str, $wordsplit = null)
    {
        if (null == $wordsplit)
        {
            $a = mb_strtolower($str, 'UTF-8');
            $a = strtr($a, array(
                    'ä' => 'ae', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'å' => 'a',
                    'ë' => 'e','é' => 'e', 'è' => 'e', 'ê' => 'e',
                    'ï' => 'i',
                    'ö' => 'oe', 'ò' => 'o', 'ó' => 'o', 'ê' => 'e',
                    'ü' => 'ue', 'ù' => 'u', 'ú' => 'u', 'û' => 'u',
                    'ß' => 'ss',
            ));
            $a = preg_replace('/\W/', '', $a);
            return $a;
        }
        else
        {
            $id = '';
            foreach (preg_split('/\s+/', $str) as $word)
            {
                $id .= (empty($id) ? '' : ' ') . self::toIdString($word,null);
            }
            return preg_replace('/\s+/', $wordsplit, trim($id));
        }
    }


    /**
     * generate one char useable for a-z lists
     *
     * @param string $string
     * @return string
     */
    public static function toAZChar($string)
    {
        $c = mb_strtoupper(mb_substr($string, 0, 1,'utf-8'), 'utf-8');
        $c = strtr($c, array(
                'Ä' => 'A', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Å' => 'A',
                'Ë' => 'E','É' => 'E', 'È' => 'E', 'Ê' => 'E',
                'Ï' => 'I',
                'Ö' => 'O', 'Ò' => 'O', 'Ó' => 'O', 'Ê' => 'E',
                'Ü' => 'U', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U',
                'ß' => 'S')
        );
        mb_regex_encoding('utf-8');
        if (! mb_ereg('[[:alpha:]]', $c))
        {
            $c = '?';
        }
        return $c;
    }

    /**
     * method to dump data as XML string.
     *
     * @see dataDumper($data, $level)
     *
     * @param mixed $data data to dump
     * @param int $level recursion level
     * @return string
     */
    public static function dataDumperXml($data)
    {
        return htmlspecialchars(self::dataDumper($data));
    }


    /**
     * method to dump data as string. The method avoid problems with the
     * recursive AgaviContext in some instances. Method can be used as
     * a alternative to print_r($data,TRUE)
     *
     * @param mixed $data data to dump
     * @param int $level recursion level
     * @return string
     */
    public static function dataDumper($data, $level = 0)
    {
        $ret = NULL;
        if ($data === NULL)
        {
            $ret = '[NULL]';
        }
        else if ($data === FALSE)
        {
            $ret = '[FALSE]';
        }
        else if ($data === TRUE)
        {
            $ret = '[TRUE]';
        }
        else if (is_object($data))
        {
            if (method_exists($data, '__toString'))
            {
                $ret = sprintf('[CLASS:%s=%s]', get_class($data), $data);
            }
            else if ($data instanceof DOMNode)
            {
                if ($data instanceof DOMDocument)
                {
                    $ret = $data->saveXML();
                }
                else
                {
                    $doc = new DOMDocument;
                    $domNode = $doc->importNode($data, true);
                    $doc->appendChild($domNode);
                    $ret = $doc->saveXML($domNode, LIBXML_NOXMLDECL);
                }
                $ret = sprintf('[CLASS:%s=%s', get_class($data), $ret);
            }
            else if ($data instanceof  Exo_CMS_Module)
            {
                $ret = sprintf('[CLASS:%s:Name=%s]', get_class($data), $data->getName());
            }
            else if ($data instanceof Exo_CMS_ModuleEntry)
            {
                $ret = sprintf('[CLASS:%s:%s = %s]', get_class($data), $data->getModule()->getName(), self::dataDumper($data->getValues()));
            }
            else if ($data instanceof AgaviRouting)
            {
                $ret = sprintf('[CLASS:%s: %s]', get_class($data), print_r($data->getAffectedRoutes(NULL),1));
            }
            else if ($data instanceof AgaviAttributeHolder)
            {
                $str = '';
                foreach ($data->getAttributeNamespaces() as $ns)
                {
                    $str .= sprintf(" {NS:%s = %s}", $ns, self::dataDumper($data->getAttributes($ns)));
                }
                $ret = sprintf('[CLASS:%s=%s]', get_class($data), $str);
            }
            else if ($data instanceof AgaviParameterHolder)
            {
                $ret = sprintf('[CLASS:%s=%s]', get_class($data), self::dataDumper($data->getParameters()));
            }
            else if ($data instanceof AgaviContext)
            {
                $ret = sprintf('[CLASS:AgaviContext=%s]', $data->getName());
            }
            else if ($data instanceof DateTime)
            {
                $ret =sprintf('[CLASS:%s=%s]', get_class($data), $data->format('r'));
            }
            else
            {
                $vars = get_object_vars($data);
                foreach ($vars as $key => $val)
                {
                    if (is_array($val) || is_object($val))
                    {
                        $vars[$key] = self::dataDumper($val, $level+1);
                    }
                }
                $ret = '[CLASS:'.get_class($data).'='.print_r($vars, TRUE).']';
            }
        }
        else if (is_array($data))
        {
            $vars = array();
            foreach ($data as $key => $val)
            {
                $vars[$key] = self::dataDumper($val, $level+1);
            }
            $ret = print_r($vars, TRUE);
        }
        else
        {
            $ret = $data;
        }
        return trim(preg_replace('/^/m', str_repeat('  ', $level), $ret));;
    }

}
