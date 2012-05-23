<?php
/**
 * DocBlox
 *
 * PHP Version 5
 *
 * @category   DocBlox
 * @package    Transformer
 * @subpackage Behaviours
 * @author     Mike van Riel <mike.vanriel@naenius.com>
 * @copyright  2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://docblox-project.org
 */

/**
 * Behaviour that links to email addresses in the @author tag.
 *
 * PHP Version 5
 *
 * @category   DocBlox
 * @package    Transformer
 * @subpackage Behaviours
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @author     Mike van Riel <mike.vanriel@naenius.com>
 * @copyright  2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://docblox-project.org
 */
class DocBlox_Plugin_Core_Transformer_Behaviour_Tag_Author extends
    DocBlox_Transformer_Behaviour_Abstract
{
    /**
     * Find all return tags that contain 'self' or '$this' and replace those
     * terms for the name of the current class' type.
     *
     * @param DOMDocument $xml Structure source to apply behaviour onto.
     *
     * @return DOMDocument
     */
    public function process(DOMDocument $xml)
    {
        $this->log('Linking to email addresses in @author tags');

        // matches:
        // - foo@bar.com
        // - <foo@bar.com>
        // - Some Name <foo@bar.com>
        // ignores leading and trailing whitespace
        // requires angled brackets when a name is given (that's what the
        //   two (?(1)) conditions do)
        // requires closing angled bracket if email address is given with an
        //   opening angled bracket but no name (that's what the (?(3))
        //   condition is for)
        $regex = '#^\s*(?P<name>[^<]+?)?\s*((?(1)<|<?)(?:mailto:)?'
            .'(?P<email>\b[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4}\b)'
            .'(?(1)>|(?(3)>|>?)))\s*$#u';

        $xpath = new DOMXPath($xml);
        $nodes = $xpath->query('//tag[@name="author"]/@description');

        /** @var DOMElement $node */
        foreach ($nodes as $node) {

            // FIXME: #193
            if (preg_match($regex, $node->nodeValue, $matches)) {
                if ($matches['name']) {
                    $value = $matches['name'];
                } else {
                    // in case there were <> but no name... this cleans up the
                    // output a bit
                    $value = $matches['email'];
                }

                $node->nodeValue = $value;
                $node->parentNode->setAttribute(
                    'link',
                    'mailto:' . $matches['email']
                );
            }
        }

        return $xml;
    }

}