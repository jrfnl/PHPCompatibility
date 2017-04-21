<?php
/**
 * \PHPCompatibility\Sniffs\PHP\ForbiddenBreakContinueOutsideLoop.
 *
 * PHP version 7.0
 *
 * @category PHP
 * @package  PHPCompatibility
 * @author   Juliette Reinders Folmer <phpcompatibility_nospam@adviesenzo.nl>
 */

namespace PHPCompatibility\Sniffs\PHP;

use PHPCompatibility\Sniff;

/**
 * \PHPCompatibility\Sniffs\PHP\ForbiddenBreakContinueOutsideLoop.
 *
 * Forbids use of break or continue statements outside of looping structures.
 *
 * PHP version 7.0
 *
 * @category PHP
 * @package  PHPCompatibility
 * @author   Juliette Reinders Folmer <phpcompatibility_nospam@adviesenzo.nl>
 */
class ForbiddenBreakContinueOutsideLoopSniff extends Sniff
{

    /**
     * Token codes of control structures in which usage of break/continue is valid.
     *
     * @var array
     */
    protected $validLoopTokens = array(
        T_FOR     => T_FOR,
        T_FOREACH => T_FOREACH,
        T_WHILE   => T_WHILE,
        T_DO      => T_DO,
        T_SWITCH  => T_SWITCH,
    );

    /**
     * Token codes of control structures in which usage of break/continue is *not* valid,
     * but which can be nested within control structures in which it *is* valid, without
     * invalidating the break/continue.
     *
     * Sounds more complicated than it is, but comes down to this:
     * - a `break` within a closure within a loop would not be valid.
     * - a `continue` within a try/catch within a loop *would* be valid.
     *
     * This array is used to distinguish between those cases.
     *
     * @var array
     */
    protected $otherAllowedTokens = array(
        T_IF      => T_IF,
        T_ELSE    => T_ELSE,
        T_ELSEIF  => T_ELSEIF,
        T_TRY     => T_TRY,
        T_CATCH   => T_CATCH,
        T_FINALLY => T_FINALLY,
        T_CASE    => T_CASE,
        T_DEFAULT => T_DEFAULT,
    );

    /**
     * All control structure tokens to use when checking for non-scoped control structures.
     *
     * Set from within the register method().
     *
     * @var array
     */
    protected $controlStructures = array();

    /**
     * Token codes which did not correctly get a condition assigned in older PHPCS versions.
     *
     * @var array
     */
    protected $backCompat = array(
        T_CASE    => true,
        T_DEFAULT => true,
    );

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        // Set the $controlStructures property only once and keep the array keys.
        $this->controlStructures = $this->validLoopTokens + $this->otherAllowedTokens;

        return array(
            T_BREAK,
            T_CONTINUE,
        );

    }//end register()

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                   $stackPtr  The position of the current token in the
     *                                         stack passed in $tokens.
     *
     * @return void
     */
    public function process(\PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];


        /*
         * Check if the break/continue is within a valid scoped loop structure.
         */
        if ($phpcsFile->hasCondition($stackPtr, $this->validLoopTokens) === true) {

            // Walk up the conditions to make sure the loop structure does not contain
            // a nested scoped non-control structure.
            $conditions = array_keys($tokens[$stackPtr]['conditions']);

            while (empty($conditions) === false) {
                $ptr = array_pop($conditions);

                if (isset($tokens[$ptr]) === false) {
                    // Shouldn't happen, but ignore if it does.
                    continue;
                }

                if (isset($this->validLoopTokens[$tokens[$ptr]['code']]) === true) {
                    // Ok, reached the valid scope condition without encountering non valid ones.
                    // I.e. this break/continue is fine.
                    return;
                }

                if (isset($this->controlStructures[$tokens[$ptr]['code']]) === false) {
                    // Ok, encountered a non-valid scoped non-loop structure.
                    break;
                }
            }
            unset($ptr, $conditions);
        } else {
            // Deal with older PHPCS versions.
            if (isset($token['scope_condition']) === true && isset($this->backCompat[$tokens[$token['scope_condition']]['code']]) === true) {
                return;
            }
        }

        // If we're still here, no valid loop structure container has been found, so throw an error.
        $error     = "Using '%s' outside of a loop or switch structure is invalid";
        $isError   = false;
        $errorCode = 'Found';
        $data      = array($token['content']);

        if ($this->supportsAbove('7.0')) {
            $error    .= ' and will throw a fatal error since PHP 7.0';
            $isError   = true;
            $errorCode = 'FatalError';
        }

        $this->addMessage($phpcsFile, $error, $stackPtr, $isError, $errorCode, $data);

    }//end process()

}//end class
