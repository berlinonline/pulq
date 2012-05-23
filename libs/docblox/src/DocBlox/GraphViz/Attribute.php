<?php
/**
 * DocBlox
 *
 * PHP Version 5
 *
 * @category  DocBlox
 * @package   GraphViz
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://docblox-project.org
 */

/**
 * Class representing a single GraphViz attribute.
 *
 * @category  DocBlox
 * @package   GraphViz
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://docblox-project.org
 */
class DocBlox_GraphViz_Attribute
{
    /** @var string The name of this attribute */
    protected $key = '';

    /** @var string The value of this attribute*/
    protected $value = '';

    /**
     * Creating a new attribute.
     *
     * @param string $key   Id for the new attribute.
     * @param string $value Value for this attribute,
     */
    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Sets the key for this attribute.
     *
     * @param string $key The new name of this attribute.
     *
     * @return DocBlox_GraphViz_Attribute
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Returns the name for this attribute.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Sets the value for this attribute.
     *
     * @param string $value The new value.
     *
     * @return DocBlox_GraphViz_Attribute
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Returns the value for this attribute.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the attribute definition as is requested by GraphViz.
     *
     * @return string
     */
    function __toString()
    {
        $key = $this->getKey();
        if ($key == 'url') {
            $key = 'URL';
        }

        $value = $this->getValue();
        if (!isset($value[0]) || $value[0] != '<') {
            $value = '"' . addslashes($value) . '"';
        }
        return $key . '=' . $value;
    }
}
