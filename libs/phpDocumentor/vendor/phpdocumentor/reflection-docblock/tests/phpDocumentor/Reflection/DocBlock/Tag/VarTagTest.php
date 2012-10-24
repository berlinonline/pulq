<?php
/**
 * phpDocumentor Var Tag Test
 *
 * @author     Daniel O'Connor <daniel.oconnor@gmail.com>
 * @copyright  Copyright (c) 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 */

namespace phpDocumentor\Reflection\DocBlock\Tag;

require_once __DIR__ . '/../../../../../src/phpDocumentor/Reflection/DocBlock/Tag/VarTag.php';

/**
 * Test class for phpDocumentor_Reflection_DocBlock_Tag_Link
 *
 * @author     Daniel O'Connor <daniel.oconnor@gmail.com>
 * @copyright  Copyright (c) 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 */
class VarTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the \phpDocumentor\Reflection\DocBlock\Tag\VarTag can understand
     * the @var doc block
     *
     * @param string $type
     * @param string $content
     * @param string $exType
     * @param string $exVariable
     * @param string $exDescription
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\VarTag::__construct
     * @dataProvider provideDataForConstuctor
     *
     * @return void
     */
    public function testConstructorParesInputsIntoCorrectFields(
        $type, $content, $exType, $exVariable, $exDescription
    )
    {
        $tag = new VarTag($type, $content);

        $this->assertEquals($exType, $tag->getType());
        $this->assertEquals($exVariable,  $tag->getVariableName());
        $this->assertEquals($exDescription,  $tag->getDescription());
    }

    /**
     * Data provider for testConstructorParesInputsIntoCorrectFields
     *
     * @return array
     */
    public function provideDataForConstuctor()
    {
        // $type, $content
        return array(
            array(
                'var',
                'int',
                'int',
                '',
                ''
            ),
            array(
                'var',
                'int $bob',
                'int',
                '$bob',
                ''
            ),
            array(
                'var',
                'int $bob Number of bobs',
                'int',
                '$bob',
                'Number of bobs'
            ),
        );
    }
}
