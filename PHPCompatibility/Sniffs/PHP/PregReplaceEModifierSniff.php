<?php
/**
 * PHPCompatibility_Sniffs_PHP_PregReplaceEModifierSniff.
 *
 * PHP version 5.6
 *
 * @category  PHP
 * @package   PHPCompatibility
 * @author    Wim Godden <wim.godden@cu.be>
 * @copyright 2014 Cu.be Solutions bvba
 */

/**
 * PHPCompatibility_Sniffs_PHP_PregReplaceEModifierSniff.
 *
 * @category  PHP
 * @package   PHPCompatibility
 * @author    Wim Godden <wim.godden@cu.be>
 * @version   1.1.0
 * @copyright 2014 Cu.be Solutions bvba
 */
class PHPCompatibility_Sniffs_PHP_PregReplaceEModifierSniff extends PHPCompatibility_Sniff
{

    /**
     * Functions to check for.
     *
     * @var array
     */
    protected $functions = array(
        'preg_replace' => true,
        'preg_filter'  => true,
    );

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_STRING);
    }//end register()

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        if ($this->supportsAbove('5.5')) {
            $tokens = $phpcsFile->getTokens();

            if ( isset($this->functions[$tokens[$stackPtr]['content']]) === false ) {
                return;
            } else {

                $openBracket = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);

                if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
                    return;
                }

                $firstParam = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($openBracket + 1), null, true);

                /**
                 * If argument is not a string, then skip test (e.g. if variable passed in).
                 */
                if ($tokens[$firstParam]['code'] !== T_CONSTANT_ENCAPSED_STRING) {
                    return;
                }

                /**
                 * Regex is a T_CONSTANT_ENCAPSED_STRING, so we need to remove the quotes
                 */
                $regex = "";
                while (isset($tokens[$firstParam]) && $tokens[$firstParam]['code'] != T_COMMA) {
                    if ($tokens[$firstParam]['code'] == T_CONSTANT_ENCAPSED_STRING) {
                        $regex .= $tokens[$firstParam]['content'];
                    }
                    $firstParam++;
                }

                $doublesSeparators = array(
                    '{' => '}',
                    '[' => ']',
                    '(' => ')',
                    '<' => '>',
                );

                $regex = substr($regex, 1, -1);

                $regexFirstChar = substr($regex, 0, 1);
                $regexEndPos = (array_key_exists($regexFirstChar, $doublesSeparators)) ?
                                    strrpos($regex, $doublesSeparators[$regexFirstChar])
                                    : strrpos($regex, $regexFirstChar);

                if($regexEndPos) {
                    $modifiers = substr($regex, $regexEndPos + 1);

                    if (strpos($modifiers, "e") !== false) {
                        if ($this->supportsAbove('7.0')) {
                            $error = 'preg_replace() - /e modifier is forbidden in PHP 7.0';
                        } else {
                            $error = 'preg_replace() - /e modifier is deprecated in PHP 5.5';
                        }
                        $phpcsFile->addError($error, $stackPtr);
                    }
                }
            }
        }


    }//end process()

}//end class
