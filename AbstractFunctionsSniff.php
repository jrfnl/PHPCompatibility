<?php
/**
 * PHPCompatibility_AbstractFunctionsSniff.
 *
 * PHP version 7.0
 *
 * @category  PHP
 * @package   PHPCompatibility
 * @author    Wim Godden <wim.godden@cu.be>
 */

/**
 * PHPCompatibility_AbstractFunctionsSniff.
 *
 * @category  PHP
 * @package   PHPCompatibility
 * @author    Wim Godden <wim.godden@cu.be>
 */
abstract class PHPCompatibility_AbstractFunctionsSniff extends PHPCompatibility_Sniff
{

    /**
     * If true, forbidden functions will be considered regular expressions.
     *
     * @var bool
     */
    protected $patternMatch = false;

    /**
     * A list of deprecated/forbidden functions with their alternatives.
     *
     * The array lists : version number with 0 (deprecated) or 1 (forbidden) and an alternative function.
     * If no alternative exists, it is NULL. IE, the function should just not be used.
     *
     * @var array(string => array(string => int|string|null))
     */
    //protected $forbiddenFunctions = array();

    private $functionInfo  = array();
    protected $functionNames = array();

    /**
     * Retrieve the information on the functions this sniff deals with.
     *
     * @return array
     */
    abstract public function getFunctionInfo();

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
		$this->functionInfo = $this->getFunctions();

        // Everyone has had a chance to figure out what forbidden functions
        // they want to check for, so now we can cache out the list.
        $this->functionNames = array_keys($this->functionInfo);

        if ($this->patternMatch === true) {
            foreach ($this->functionNames as $i => $name) {
                $this->functionNames[$i] = '/'.$name.'/i';
            }

            return array(T_STRING);
        }

        // If we are not pattern matching, we need to work out what
        // tokens to listen for.
        $string = '<?php ';
        foreach ($this->functionNames as $name) {
            $string .= $name.'();';
        }

        $register = array();

        $tokens = token_get_all($string);
        array_shift($tokens);
        foreach ($tokens as $token) {
            if (is_array($token) === true) {
                $register[] = $token[0];
            }
        }

        $this->functionNames = array_map('strtolower', $this->functionNames);
        $this->functionInfo  = array_combine($this->functionNames, $this->functionInfo);

        return array_unique($register);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($this->isFunctionCall($phpcsFile, $stackPtr) === false ) {
            // Not a call to a PHP function.
            return;
        }

        $function = strtolower($tokens[$stackPtr]['content']);
        $pattern  = null;

        if ($this->patternMatch === true) {
            $count   = 0;
            $pattern = preg_replace(
                    $this->forbiddenFunctionNames,
                    $this->forbiddenFunctionNames,
                    $function,
                    1,
                    $count
            );

            if ($count === 0) {
                return;
            }

            // Remove the pattern delimiters and modifier.
            $pattern = substr($pattern, 1, -2);
        } else {
            if (in_array($function, $this->forbiddenFunctionNames) === false) {
                return;
            }
        }

        $this->addError($phpcsFile, $stackPtr, $function, $pattern);

    }//end process()

    /**
     * Generates the error or wanrning for this sniff.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the forbidden function
     *                                        in the token array.
     * @param string               $function  The name of the forbidden function.
     * @param string               $pattern   The pattern used for the match.
     *
     * @return void
     */
    protected function addError($phpcsFile, $stackPtr, $function, $pattern=null)
    {
        if ($pattern === null) {
            $pattern = $function;
        }

        $error = '';

        $isError = false;
        $previousVersionStatus = null;
        foreach ($this->forbiddenFunctions[$pattern] as $version => $forbidden) {
            if ($this->supportsAbove($version)) {
                if ($version != 'alternative') {
                    if ($previousVersionStatus !== $forbidden) {
                        $previousVersionStatus = $forbidden;
                        if ($forbidden === true) {
                            $isError = true;
                            $error .= 'forbidden';
                        } else {
                            $error .= 'discouraged';
                        }
                        $error .=  ' from PHP version ' . $version . ' and ';
                    }
                }
            }
        }
        if (strlen($error) > 0) {
            $error = 'The use of function ' . $function . ' is ' . $error;
            $error = substr($error, 0, strlen($error) - 5);

            if ($this->forbiddenFunctions[$pattern]['alternative'] !== null) {
                $error .= '; use ' . $this->forbiddenFunctions[$pattern]['alternative'] . ' instead';
            }

            if ($isError === true) {
                $phpcsFile->addError($error, $stackPtr);
            } else {
                $phpcsFile->addWarning($error, $stackPtr);
            }
        }

    }//end addError()

}//end class
