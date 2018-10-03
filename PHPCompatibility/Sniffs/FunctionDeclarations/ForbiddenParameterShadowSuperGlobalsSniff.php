<?php
/**
 * PHPCompatibility, an external standard for PHP_CodeSniffer.
 *
 * @package   PHPCompatibility
 * @copyright 2012-2018 PHPCompatibility Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCompatibility/PHPCompatibility
 */

namespace PHPCompatibility\Sniffs\FunctionDeclarations;

use PHPCompatibility\Sniff;
use PHPCompatibility\PHPCSHelper;
use PHP_CodeSniffer_File as File;

/**
 * \PHPCompatibility\Sniffs\FunctionDeclarations\ForbiddenParameterShadowSuperGlobalsSniff
 *
 * Discourages use of superglobals as parameters for functions.
 *
 * {@internal List of superglobals is maintained in the parent class.}}
 *
 * PHP version 5.4
 *
 * @category  PHP
 * @package   PHPCompatibility
 * @author    Declan Kelly <declankelly90@gmail.com>
 * @copyright 2015 Declan Kelly
 */
class ForbiddenParameterShadowSuperGlobalsSniff extends Sniff
{

    /**
     * Register the tokens to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            \T_FUNCTION,
            \T_CLOSURE,
        );
    }

    /**
     * Processes the test.
     *
     * @param \PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                   $stackPtr  The position of the current token.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        if ($this->supportsAbove('5.4') === false) {
            return;
        }

        // Get all parameters from function signature.
        $parameters = PHPCSHelper::getMethodParameters($phpcsFile, $stackPtr);
        if (empty($parameters) || \is_array($parameters) === false) {
            return;
        }

        foreach ($parameters as $param) {
            if (isset($this->superglobals[$param['name']]) === true) {
                $error     = 'Parameter shadowing super global (%s) causes fatal error since PHP 5.4';
                $errorCode = $this->stringToErrorCode(substr($param['name'], 1)) . 'Found';
                $data      = array($param['name']);

                $phpcsFile->addError($error, $param['token'], $errorCode, $data);
            }
        }
    }
}
