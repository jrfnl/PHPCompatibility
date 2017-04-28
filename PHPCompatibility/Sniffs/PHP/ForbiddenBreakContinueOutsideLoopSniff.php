<?php
/**
 * \PHPCompatibility\Sniffs\PHP\ForbiddenBreakContinueOutsideLoop.
 *
 * PHP version 7.0
 *
 * @category PHP
 * @package  PHPCompatibility
 * @author	 Juliette Reinders Folmer <phpcompatibility_nospam@adviesenzo.nl>
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
 * @author	 Juliette Reinders Folmer <phpcompatibility_nospam@adviesenzo.nl>
 */
class ForbiddenBreakContinueOutsideLoopSniff extends Sniff
{

	/**
	 * Token codes of control structures in which usage of break/continue is valid.
	 *
	 * @var array
	 */
	protected $validLoopTokens = array(
		T_FOR	  => T_FOR,
		T_FOREACH => T_FOREACH,
		T_WHILE   => T_WHILE,
		T_DO	  => T_DO,
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
		T_IF	  => T_IF,
		T_ELSE	  => T_ELSE,
		T_ELSEIF  => T_ELSEIF,
		T_TRY	  => T_TRY,
		T_CATCH   => T_CATCH,
		T_FINALLY => T_FINALLY,
		T_CASE	  => T_CASE,
		T_DEFAULT => T_DEFAULT,
	);

	/**
	 * If/else structure tokens.
	 *
	 * May not be scoped as braces are only required for multi-statement.
	 *
	 * @var array
	 */
	protected $ifElseTokens = array(
		T_IF	 => T_IF,
		T_ELSE	 => T_ELSE,
		T_ELSEIF => T_ELSEIF,
	);

	/**
	 * Try/catch structure tokens.
	 *
	 * Will always be scoped as braces are required.
	 *
	 * @var array
	 */
	protected $tryCatchTokens = array(
		T_TRY	  => T_TRY,
		T_CATCH   => T_CATCH,
		T_FINALLY => T_FINALLY,
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
		T_CASE	  => true,
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
ini_set( 'xdebug.overload_var_dump', 1 );
/*
static $dumped = false;
if($dumped === false) {
	echo "\n";
	foreach( $tokens as $ptr => $token ) {
		if ( ! isset( $token['length'] ) ) {
			$token['length'] = strlen($token['content']);
		}
		echo $ptr . ' :: L' . str_pad( $token['line'] , 3, '0', STR_PAD_LEFT ) . ' :: C' . $token['column'] . ' :: ' . $token['type'] . ' :: (' . $token['length'] . ') :: ' . $token['content'] . "\n";
//		  if ( $token['code'] === T_WHILE || $token['code'] === T_DO || $token['code'] === T_FUNCTION ) {
//			  var_dump( $token );
//		  }
	}
	unset( $ptr, $token );
	$dumped = true;
}
*/
		$token	= $tokens[$stackPtr];

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
					// It may still contain a non-scoped valid control structure, but we'll check for that later.
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

		// Oh dear, hope nobody is using a control structure without braces...
		
		// The break/continue may be within a scoped structure which creates a hard limit,
		// like a closure or anonymous class.
		// In that case, we need to take this hard limit into account when checking for non-scoped
		// control structures.
		$hardLimitStart = 0;
		$hardLimitEnd   = ($phpcsFile->numTokens + 1);
		if (empty($tokens[$stackPtr]['conditions']) === false) {
			$conditions = array_keys($tokens[$stackPtr]['conditions']);

			while (empty($conditions) === false) {
				$ptr = array_pop($conditions);

				if (isset($tokens[$ptr]) === false) {
					// Shouldn't happen, but ignore if it does.
					continue;
				}

				if (isset($this->controlStructures[$tokens[$ptr]['code']]) === false) {
					if (isset($tokens[$ptr]['scope_opener'], $tokens[$ptr]['scope_closer']) === true) {
						$hardLimitStart = $tokens[$ptr]['scope_opener'];
						$hardLimitEnd   = $tokens[$ptr]['scope_closer'];
					}
					break;
				}
			}
			unset($ptr, $conditions);
		}


echo "\nStarting new check for token {$stackPtr} - type {$tokens[$stackPtr]['type']}\n";
		if ($this->isInNonScopedLoopStructure($phpcsFile, $stackPtr, $hardLimitStart, $hardLimitEnd) === true) {
			return;
		}

		// Ok, so this break/continue is not without a scoped structure. It may be a one-liner without braces.
		/*
		check for a structure on the same line or the line before.
		if not found, see if the containing structure is a control structure like if and if so, check the same line or the line before that.
		*/


		// If we're still here, no valid loop structure container has been found, so throw an error.
		$error	   = "Using '%s' outside of a loop or switch structure is invalid";
		$isError   = false;
		$errorCode = 'Found';
		$data	   = array($token['content']);

		if ($this->supportsAbove('7.0')) {
			$error	  .= ' and will throw a fatal error since PHP 7.0';
			$isError   = true;
			$errorCode = 'FatalError';
		}

		$this->addMessage($phpcsFile, $error, $stackPtr, $isError, $errorCode, $data);

	}//end process()


	/**
	 * Check whether a token - of which we already know it's not in a scoped loop structure -
	 * is in a non-scoped loop structure.
	 *
	 * In that case, the token has to be in the first statement after the start of the loop structure.
	 * Or the token has to be in an if/else or try/catch structure - either scoped or non-scoped - and
	 * the if/else or try/catch has to be the first statement after the start of the loop structure.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int				   $stackPtr  The position of the current token in the
	 *										  stack passed in $tokens.
	 *
	 * @return bool
	 */
	protected function isInNonScopedLoopStructure(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $hardLimitStart, $hardLimitEnd)
	{
		static $statementClosers = array(
			T_COLON 			  => T_COLON,
			T_SEMICOLON 		  => T_SEMICOLON,
			T_OPEN_CURLY_BRACKET  => T_OPEN_CURLY_BRACKET,
			T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
			T_OPEN_TAG			  => T_OPEN_TAG,
			T_CLOSE_TAG 		  => T_CLOSE_TAG,
		);

		$tokens = $phpcsFile->getTokens();


//echo "\nChecking for non scoped {$stackPtr} - type {$tokens[$stackPtr]['type']}\n";
		$prevControlStructure = $phpcsFile->findPrevious($this->controlStructures, ($stackPtr - 1), $hardLimitStart);
		
		/*
		-> if it's a loop construct
		   -> make sure the prevControlStructure found is not scoped as we already know it shouldn't be.
		   -> check to make sure that the stackPtr is in the next statement
		   
		-> if not a loop construct
		   -> if non-scoped -> check to make sure that the stackPtr is in the next statement
		   	  -> if an if/else try/catch walk up to the beginning
		   	  	 -> check that the beginning if after $hardLimitStart


		*/

//var_dump($tokens[$prevControlStructure]);
/*
		if (isset($tokens[$prevControlStructure]['parenthesis_closer']) === false) {
			if ($tokens[$prevControlStructure]['code'] !== T_DO
				&& $tokens[$prevControlStructure]['code'] !== T_ELSE
				&& $tokens[$prevControlStructure]['code'] !== T_TRY
				&& $tokens[$prevControlStructure]['code'] !== T_FINALLY
			) {
				// Possible parse error.
				return false;
			}

			$endOfCondition = $prevControlStructure;

		} else {
			$endOfCondition = $tokens[$prevControlStructure]['parenthesis_closer'];
		}
*/
/*		$nextEndOfStatement = $phpcsFile->findNext($statementClosers, ($endOfCondition + 1));

		// Check if it's a loop structure and the next statement contains our token.
		if (isset($this->validLoopStructures[$tokens[$prevControlStructure]['code']]) === true) {
			if ($endOfCondition < $stackPtr && $stackPtr < $nextEndOfStatement) {
				return true;
			}

			return false;
		}
*/
		/*
		 * Ok, not a loop structure, so this must be an if/else or try/catch structure.
		 */
		if (isset($this->tryCatchTokens[$tokens[$prevControlStructure]['code']]) === true) {
			$prev = $this->findStartOfTryCatch($phpcsFile, $prevControlStructure);
		}
		elseif (isset($this->ifElseTokens[$tokens[$prevControlStructure]['code']]) === true) {
			$prev = $this->findStartOfIfElse($phpcsFile, $prevControlStructure);
		}
		if ($prev === false) {
			return false;
		}
var_dump( $prev);
var_dump( $tokens[$prev] );

exit;

		// Make sure we only consider if/else statements which are scoped and
		// where the stackPtr is in the next statement.
/*
		if ((empty($tokens[$prevControlStructure]['scope_condition']) === true
				|| $tokens[$prevControlStructure]['scope_condition'] !== $prevControlStructure)
			&& ($endOfCondition >= $stackPtr || $stackPtr >= $nextEndOfStatement)
		) {
//echo "\nFalse as not next statement in non-scoped if\n";
			return false;
		}
*/
/*
		$prevIf = $this->findStartOfIfElse($phpcsFile, $prevControlStructure);
		if ($prevIf === false) {
			// Hmm.. not an if/else. In that case, just throw the error.
//echo "\nFalse as couldn't determine prev if\n";
			return false;
		}
*/
		// Recurse back into this function with the found if token.
		return $this->isInNonScopedLoopStructure($phpcsFile, $prevIf);
	}


	/**
	 * Find the start of an if/elseif/else statement.
	 *
	 * If/elseif/else statements are may or may not be scoped, so we need to do some careful token walking.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int				   $stackPtr  The position of an if/elseif/else token.
	 *
	 * @return bool|int StackPtr to the corresponding T_IF token if found, false if not.
	 */
	protected function findStartOfIfElse(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();

		if (isset($this->ifElseTokens[$tokens[$stackPtr]['code']]) === false) {
			return false;
		}

		// If this is an if statement, check to make sure it's not preceeded by an else.
		if ($tokens[$stackPtr]['code'] === T_IF) {
			$prev = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr - 1), null, true, null, true);
			if ($prev === false || $tokens[$prev]['code'] !== T_ELSE) {
				return $stackPtr;
			}

			// Set the stackPtr back to the else.
			$stackPtr = $prev;
		}
//var_dump( $tokens[$stackPtr] );

		// Ok, so this is an else or elseif.
		$scopeLevel = count($tokens[$stackPtr]['conditions']);
		do {
			$prev = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr - 1), null, true);
			if ($prev !== false && $tokens[$prev]['code'] === T_CLOSE_CURLY_BRACKET
				&& isset($tokens[$prev]['scope_opener'], $tokens[$prev]['scope_closer']) === true
				&& $tokens[$prev]['scope_closer'] === $prev
			) {
				// Set the stackPtr back to the scope opener of the scoped condition directly
				// preceeding the else/elseif to ignore any control structures within the scope.
				$stackPtr = $tokens[$prev]['scope_opener'];
			}
	
	
			$prevIf = $phpcsFile->findPrevious($this->ifElseTokens, ($stackPtr - 1));
	
			if ($prevIf === false) {
				// Something very weird is going on as an else/elseif should always be preceeded by an if/elseif.
				return false;
			}

			$stackPtr = $prevIf;

		} while(count($tokens[$stackPtr]['conditions']) > $scopeLevel);



		// We still need something here to distinquish between scoped and non-scoped.

		// Check if the found if has itself an if as its last scope condition.
		// If it does, check whether the else is within the scope.
		   // If so -> use the found if
		   // If not, loop further back.

		/*
		check if the stackPtr has conditions and if the last condition is an if/else/elseif (scoped)
			if so, if it's an if, check if it's preceeded by else
			   if so or if it is an else or elseif statement, find the previous if (and do the same again)
				  if we've found the original if -> recurse into this function

		if the stackPtr does not have conditions, check if the preceeding controlstructure is in if/else etc

		 is scoped & if so, if the continue/break is within the scope.
		-> if an if statement, recurse
		*/

		/*
		 * If the previous if is in a scoped if/else, check if it is on the same
		 * level as the $stackPtr and if not, walk back up until it is.
		 */
/*
		if (empty($tokens[$prevIf]['conditions']) === false) {
			$conditions = array_keys($tokens[$stackPtr]['conditions']);

			while (empty($conditions) === false) {
				$ptr = array_pop($conditions);

				if (isset($tokens[$ptr]) === false) {
					break;
				}

				if (isset($this->nonLoopStructures[$tokens[$ptr]['type']]) === false) {
					break;
				}

				if (isset($tokens[$ptr]['scope_closer']) === false || $tokens[$ptr]['scope_closer'] > $stackPtr) {
					break;
				}

				$prevIf = $ptr;
			}
		}
*/

		// SCRATCH THE ABOVE
		// Check for a curly close brace as prevNonEmpty before the else/elseif
		// only start searching for the prevIf from the opening brace backwards.
		// Also check the condition count of the if/else to make sure we have one on the same scoped level.

		return $this->findStartOfIfElse($phpcsFile, $stackPtr);
	}


	/**
	 * Find the start of a try/catch/finally statement.
	 *
	 * Try/catch/finally statements are always scoped, so simply a case of walking up.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int				   $stackPtr  The position of a try/catch/finally token.
	 *
	 * @return bool|int StackPtr to the corresponding T_TRY token if found, false if not.
	 */
	protected function findStartOfTryCatch(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();

		if (isset($this->tryCatchTokens[$tokens[$stackPtr]['code']]) === false) {
			return false;
		}

		if ($tokens[$stackPtr]['code'] === T_TRY) {
			return $stackPtr;
		}

		// Ok, so this is a catch or finally.
		$prev = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr - 1), null, true);
		if ($prev !== false && $tokens[$prev]['code'] === T_CLOSE_CURLY_BRACKET
			&& isset($tokens[$prev]['scope_opener'], $tokens[$prev]['scope_closer']) === true
			&& $tokens[$prev]['scope_closer'] === $prev
		) {
			$stackPtr = $tokens[$prev]['scope_opener'];
		}


		$prevOfType = $phpcsFile->findPrevious($this->tryCatchTokens, ($stackPtr - 1));

		if ($prevOfType === false) {
			// Something very weird is going on as a catch/finally should always be preceeded by a try/catch.
			return false;
		}

		return $this->findStartOfTryCatch($phpcsFile, $prevOfType);
	}

}//end class
