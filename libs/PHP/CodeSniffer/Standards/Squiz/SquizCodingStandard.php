<?php
/**
 * Squiz Coding Standard.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: SquizCodingStandard.php 291897 2009-12-08 22:13:49Z squiz $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if (class_exists('PHP_CodeSniffer_Standards_CodingStandard', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_CodingStandard not found');
}

/**
 * Squiz Coding Standard.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.2.2
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class PHP_CodeSniffer_Standards_Squiz_SquizCodingStandard extends PHP_CodeSniffer_Standards_CodingStandard
{


    /**
     * Return a list of external sniffs to include with this standard.
     *
     * The Squiz standard uses some generic sniffs, and also borrows from the
     * the PEAR standard.
     *
     * @return array
     */
    public function getIncludedSniffs()
    {
        return array(
                'Generic/Sniffs/CodeAnalysis/UnusedFunctionParameterSniff.php',
                'Generic/Sniffs/Commenting/TodoSniff.php',
                'Generic/Sniffs/ControlStructures/InlineControlStructureSniff.php',
                'Generic/Sniffs/Formatting/DisallowMultipleStatementsSniff.php',
                'Generic/Sniffs/Formatting/SpaceAfterCastSniff.php',
                'Generic/Sniffs/NamingConventions/ConstructorNameSniff.php',
                'Generic/Sniffs/NamingConventions/UpperCaseConstantNameSniff.php',
                'Generic/Sniffs/PHP/DisallowShortOpenTagSniff.php',
                'Generic/Sniffs/Strings/UnnecessaryStringConcatSniff.php',
                'Generic/Sniffs/WhiteSpace/DisallowTabIndentSniff.php',
                'PEAR/Sniffs/ControlStructures/MultiLineConditionSniff.php',
                'PEAR/Sniffs/Files/IncludingFileSniff.php',
                'PEAR/Sniffs/Files/LineLengthSniff.php',
                'PEAR/Sniffs/Formatting/MultiLineAssignmentSniff.php',
                'PEAR/Sniffs/Functions/FunctionCallArgumentSpacingSniff.php',
                'PEAR/Sniffs/Functions/FunctionCallSignatureSniff.php',
                'Zend/Sniffs/Debug/CodeAnalyzerSniff.php',
               );

    }//end getIncludedSniffs()


}//end class
?>
