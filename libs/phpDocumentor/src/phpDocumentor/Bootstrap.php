<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @category  phpDocumentor
 * @package   Core
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor;

require_once __DIR__.'/Application.php';

/**
 * This class provides a bootstrap for all application who wish to interface
 * with phpDocumentor.
 *
 * The Boostrapper is responsible for setting up the phpDocumentor autoloader, setting
 * up the event dispatcher and initializing the plugins.
 *
 * @category phpDocumentor
 * @package  Core
 * @author   Mike van Riel <mike.vanriel@naenius.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @link     http://phpdoc.org
 */
class Bootstrap
{
    /**
     * Helper static function to get an instance of this class.
     *
     * Usually used to do a one-line initialization, such as:
     *
     *     \phpDocumentor\Bootstrap::createInstance()->initialize();
     *
     * @return Bootstrap
     */
    public static function createInstance()
    {
        return new self();
    }

    /**
     * Convenience method that does the complete initialization for phpDocumentor.
     *
     * This method will register the autoloader, event dispatcher and plugins.
     * The methods called can also be implemented separately, for example when
     * you want to use your own autoloader.
     *
     * @return \phpDocumentor\Application()
     */
    public function initialize()
    {
        return new \phpDocumentor\Application();
    }
}