<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @category   phpDocumentor
 * @package    Parser
 * @subpackage Tests
 * @author     Mike van Riel <mike.vanriel@naenius.com>
 * @copyright  2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://phpdoc.org
 */

namespace phpDocumentor\Parser;

/**
 * Mock for the Layer superclass in the \phpDocumentor\Parser Component.
 *
 * @category   phpDocumentor
 * @package    Parser
 * @subpackage Tests
 * @author     Mike van Riel <mike.vanriel@naenius.com>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://phpdoc.org
 */
class AbstractMock extends ParserAbstract
{

}

/**
 * Test for the Layer superclass in the \phpDocumentor\Parser Component.
 *
 * @category   phpDocumentor
 * @package    Parser
 * @subpackage Tests
 * @author     Mike van Riel <mike.vanriel@naenius.com>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://phpdoc.org
 */
class ParserAbstractTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractMock */
    protected $fixture = null;

    /**
     * Initializes the fixture for this test.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->fixture = new AbstractMock();
    }

    /**
     * Tests the dispatch method.
     *
     * It is expected that the `dispatch` method:
     *
     * * Returns null when no EventDispatcher is set.
     * * Throws a \phpDocumentor\Parser\Exception if the event dispatcher variable
     *   contains an invalid value.
     * * The correct method of the EventDispatcher is used and the return value
     *   is correctly returned.
     *
     * @return void
     */
    public function testDispatch()
    {
        // set up mocks for the dispatcher and the generated event.
        $event_dispatcher = $this->getMock(
            'sfEventDispatcher', array('notify')
        );
        $event = $this->getMock(
            'sfEvent',
            array('getReturnValue'),
            array(
                $this->fixture,
                'system.log',
                array(
                    'priority' => \phpDocumentor\Plugin\Core\Log::ERR,
                    'message' => 'body'
                )
            )
        );

        // the event dispatcher's notify method will be invoken and return the
        // expected event
        $event_dispatcher
            ->expects($this->once())
            ->method('notify')
            ->will($this->returnValue($event));

        // we will let the event return true to test whether the return value
        // is actually returned
        $event->expects($this->once())
            ->method('getReturnValue')
            ->will($this->returnValue(true));

        // test without setting the dispatcher
        $result = $this->fixture->dispatch(
            'system.log',
            array(
                'priority' => \phpDocumentor\Plugin\Core\Log::ERR,
                'message' => 'body'
            )
        );
        $this->assertSame(
            null, $result,
            'Expected result to be null when no dispatcher is set'
        );

        // set the dispatcher
        ParserAbstract::$event_dispatcher = $event_dispatcher;

        // test with the dispatcher
        $result = $this->fixture->dispatch(
            'system.log',
            array(
                'priority' => \phpDocumentor\Plugin\Core\Log::ERR,
                'message' => 'body'
            )
        );
        $this->assertSame(
            true, $result,
            'Expected result to be true when the dispatcher mock object is set'
        );

        // if the event dispatcher is not null but also no an event dispatcher;
        // throw exception
        $this->setExpectedException('\phpDocumentor\Parser\Exception');
        ParserAbstract::$event_dispatcher = true;
        $this->fixture->dispatch(
            'system.log',
            array(
                'priority' => \phpDocumentor\Plugin\Core\Log::ERR,
                'message' => 'body'
            )
        );
    }

    /**
     * Tests the log method.
     *
     * It is expected that the `log` method,
     *
     * * invokes the event dispatcher.
     *
     * @return void
     */
    public function testLog()
    {
        // set up mocks for the dispatcher and the generated event.
        $event_dispatcher = $this->getMock(
            'sfEventDispatcher', array('notify')
        );
        $event = $this->getMock(
            'sfEvent',
            array('getReturnValue'),
            array($this->fixture, 'system.log', array(
                'message' => 'body',
                'priority' => 6
            ))
        );

        // the event dispatcher's notify method will be invoken and return the
        // expected event
        $event_dispatcher
            ->expects($this->once())
            ->method('notify')
            ->will($this->returnValue($event));

        // we will let the event return true to test whether the return value
        // is actually returned
        $event->expects($this->once())
            ->method('getReturnValue')
            ->will($this->returnValue(true));

        // test without setting the dispatcher
        ParserAbstract::$event_dispatcher = $event_dispatcher;
        $this->fixture->log('body', 6);
    }

    /**
     * Tests the debug method.
     *
     * It is expected that the `debug` method,
     *
     * * invokes the event dispatcher.
     *
     * @return void
     */
    public function testDebug()
    {
        // set up mocks for the dispatcher and the generated event.
        $event_dispatcher = $this->getMock(
            'sfEventDispatcher', array('notify')
        );
        $event = $this->getMock(
            'sfEvent',
            array('getReturnValue'),
            array($this->fixture, 'system.debug', array(
                'message' => 'body'
            ))
        );

        // the event dispatcher's notify method will be invoken and return the
        // expected event
        $event_dispatcher
            ->expects($this->once())
            ->method('notify')
            ->will($this->returnValue($event));

        // we will let the event return true to test whether the return value
        // is actually returned
        $event->expects($this->once())
            ->method('getReturnValue')
            ->will($this->returnValue(true));

        // test without setting the dispatcher
        ParserAbstract::$event_dispatcher = $event_dispatcher;
        $this->fixture->debug('body');
    }

}