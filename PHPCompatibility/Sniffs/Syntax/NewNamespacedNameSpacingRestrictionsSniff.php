<?php
/**
 * PHPCompatibility, an external standard for PHP_CodeSniffer.
 *
 * @package   PHPCompatibility
 * @copyright 2012-2020 PHPCompatibility Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCompatibility/PHPCompatibility
 */

namespace PHPCompatibility\Sniffs\Syntax;

use PHPCompatibility\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\BackCompat\BCFile;
use PHPCSUtils\Tokens\Collections;
use PHPCSUtils\Utils\GetTokensAsString;
use PHPCSUtils\Utils\NamingConventions;
use PHPCSUtils\Utils\UseStatements;

/**
 * Detect code which doesn't comply with the new namespaced name spacing rules in PHP 8.0.
 *
 * > As of PHP 8.0, whitespace [red: and comments] is not permitted between namespace separators.
 * > If it occurs, the namespace separator will be parsed as T_NS_SEPARATOR, which will subsequently
 * > lead to a parse error.
 * > It is not possible to allow whitespace, because namespaced names commonly occur next to keywords.
 *
 * The above also implies (and the RFC/PHP PR confirms) that a space or comment between a keyword and
 * a namespaced name after it, is now a requirement.
 *
 * PHP version 8.0
 *
 * @link https://wiki.php.net/rfc/namespaced_names_as_token
 *
 * @since 10.0.0
 */
class NewNamespacedNameSpacingRestrictionsSniff extends Sniff
{

    /**
     * A list of keywords which can (legitimately) be followed by a fully qualified name.
     *
     * @since 10.0.0
     *
     * @var array
     */
    protected $keywords = [
        'and'          => true,
        'as'           => true,
        'break'        => true,
        'case'         => true,
        'clone'        => true,
        'const'        => true,
        'continue'     => true,
        'echo'         => true,
        'extends'      => true,
        'function'     => true,
        'implements'   => true,
        'include'      => true,
        'include_once' => true,
        'instanceof'   => true,
        'insteadof'    => true,
        'new'          => true,
        'or'           => true,
        'print'        => true,
        'require'      => true,
        'require_once' => true,
        'return'       => true,
        'throw'        => true,
        'use'          => true,
        'xor'          => true,
        'yield from'   => true,
        'yield'        => true,
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @since 10.0.0
     *
     * @return array
     */
    public function register()
    {
        $targets         = Collections::namespacedNameTokens();
        $targets[\T_USE] = \T_USE;

        return $targets;
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @since 10.0.0
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return int|void Integer stack pointer to skip forward to. The sniff will not be
     *                  called again on the current file until the returned stack
     *                  pointer is reached.
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        if ($this->supportsAbove('8.0') === false) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        /*
         * Work around a tokenizer quirk in PHPCS 2.8.0 - 2.9.2.
         * Backslash tokenized as T_STRING when in a `use function` statement with no space
         * between the `function` keyword and the backslash.
         */
        if ($tokens[$stackPtr]['code'] === \T_STRING && $tokens[$stackPtr]['content'] === '\\') {
            $tokens[$stackPtr]['code'] = \T_NS_SEPARATOR;
            $tokens[$stackPtr]['type'] = 'T_NS_SEPARATOR';
        }

        /*
         * Work around a tokenizer issue in PHPCS 2.x in combination with low PHP versions.
         * `yield (from)` is not always correctly polyfilled.
         */
        if ($tokens[$stackPtr]['code'] === \T_STRING && \strtolower($tokens[$stackPtr]['content']) === 'yield') {
            $tokenPtr = $stackPtr;
            $content  = 'yield';
            $type     = 'T_YIELD';

            $nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);
            if ($nextNonEmpty === false) {
                return;
            }

            if (\strtolower($tokens[$nextNonEmpty]['content']) === 'from') {
                $tokenPtr = $nextNonEmpty;
                $content  = 'yield from';
                $type     = 'T_YIELD_FROM';

                $nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, ($nextNonEmpty + 1), null, true);
                if ($nextNonEmpty === false) {
                    return;
                }
            }

            if (isset(Collections::namespacedNameTokens()[$tokens[$nextNonEmpty]['code']]) !== true) {
                // Not followed by a namespaced name. Ignore.
                return;
            }

            /*
             * Set the $stackPtr to the namespaced name token and overwrite the "before" token,
             * then let the sniff do its work as if that token was the one passed to it.
             * This only "overwrites" the function local token stack.
             */
            $stackPtr                     = $nextNonEmpty;
            $tokens[$tokenPtr]['content'] = $content;
            $tokens[$tokenPtr]['type']    = $type;
        }

        /*
         * Use statements are complicated due to the variety of valid ways to write them,
         * especially with "mixed" group use statements, so handle those separately.
         */
        if ($tokens[$stackPtr]['code'] === \T_USE) {
            if (UseStatements::isImportUse($phpcsFile, $stackPtr) === false) {
                /*
                 * Not an import use statement.
                 * Closure use can be ignored either way.
                 * Trait use statements can be handled correctly on the "name" tokens.
                 */
                return;
            }

            return $this->analyseUseStatement($phpcsFile, $stackPtr);
        }

        /*
         * Handle the other tokens.
         */
        $start = $stackPtr;
        $end   = null;

        switch ($tokens[$stackPtr]['type']) {
            case 'T_NAMESPACE':
                // Namespace declaration or operator.
                $nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);

                if ($nextNonEmpty === false) {
                    // Live coding or parse error.
                    return;
                }

                if ($tokens[$nextNonEmpty]['code'] !== \T_NS_SEPARATOR
                    && $tokens[$nextNonEmpty]['type'] !== 'T_NAME_FULLY_QUALIFIED'
                ) {
                    // This must be a namespace declaration, move the pointer for the start of the name forward.
                    $start = $nextNonEmpty;

                    // Make sure the name analyzer walks to the end of the namespace declaration
                    // and ignores reserved keywords, which are allowed in namespace name since PHP 8.0.
                    $end = BCFile::findEndOfStatement($phpcsFile, $stackPtr);
                }

                break;

            case 'T_NAME_FULLY_QUALIFIED':
            case 'T_NS_SEPARATOR':
                /*
                 * Make sure that there is whitespace before the namespace separator if the preceeding
                 * token is a keyword.
                 */
                if (isset(Tokens::$emptyTokens[$tokens[($stackPtr - 1)]['code']]) === false) {
                    $this->checkSpaceBefore($phpcsFile, $tokens[($stackPtr - 1)]['content'], ($stackPtr - 1));
                }

                break;

            case 'T_STRING':
                /*
                 * If this is a run on PHP 8 in combination with a PHPCS version in which the
                 * PHP 8-tokenization is "undone", this may be a keyword, so check the spacing.
                 */
                if ($tokens[($stackPtr + 1)]['code'] === \T_NS_SEPARATOR) {
                    $this->checkSpaceBefore($phpcsFile, $tokens[$stackPtr]['content'], $stackPtr);
                }

                break;

            case 'T_NAME_QUALIFIED':
                $this->splitAndExamineQualifiedNameForKeywords($phpcsFile, $stackPtr);
                break;

            case 'T_NAME_RELATIVE':
            default:
                break;
        }

        return $this->analyseName($phpcsFile, $start, $end);
    }


    /**
     * Process import `use` statements.
     *
     * @since 10.0.0
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return int|void Integer stack pointer to skip forward to. The sniff will not be
     *                  called again on the current file until the returned stack
     *                  pointer is reached.
     */
    protected function analyseUseStatement(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $endOfStatement = $phpcsFile->findNext([\T_SEMICOLON, \T_CLOSE_TAG], ($stackPtr + 1));
        if ($endOfStatement === false) {
            return;
        }

        $isGroupUse   = $phpcsFile->findNext([\T_OPEN_USE_GROUP], ($stackPtr + 1), $endOfStatement);
        $nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);
        $contentLC    = \strtolower($tokens[$nextNonEmpty]['content']);

        if ($isGroupUse === false) {
            /*
             * Not a group use statement. Skip over potential function/const keywords
             * and then let the "name" tokens handle this statement.
             */
            if ($contentLC === 'function' || $contentLC === 'const') {
                // Skip past function/const keyword for name examination.
                return ($nextNonEmpty + 1);
            }

            return;
        }

        /*
         * Group use statement, this is a little more complicated.
         */
        $isFunctionConstGroup = ($contentLC === 'function' || $contentLC === 'const');

        if ($isFunctionConstGroup === false
            && $tokens[$nextNonEmpty]['type'] === 'T_NAME_QUALIFIED'
        ) {
            // This might be a function/const group use without space between the keyword and the name.
            $this->splitAndExamineQualifiedNameForKeywords($phpcsFile, $nextNonEmpty);
        }

        /*
         * Handle the part before the group opener.
         * We need to skip over potential function/const keywords and track back to
         * the namespace separator before it as it won't be part of the name and
         * the spacing around that namespace separator is not restricted.
         */
        if ($isFunctionConstGroup === true) {
            $nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, ($nextNonEmpty + 1), null, true);
        }

        $end = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($isGroupUse - 1), $nextNonEmpty, true);
        $this->analyseName($phpcsFile, $nextNonEmpty, $end);

        /*
         * If this was a group use statement for which we know there won't be function/const keywords
         * *within* the group, we can defer to the "name" tokens to handle the rest of the statement.
         */
        if ($isFunctionConstGroup === true) {
            return $isGroupUse;
        }

        /*
         * For a potentially "mixed" group use statement, we need to be more careful and walk it.
         * We don't need to bother with the "space before" check as a leading backslash in a
         * import inside a group use statement is a parse error in all PHP versions, so not our
         * concern.
         */
        $nextNonEmpty = $isGroupUse;
        do {
            $nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, ($nextNonEmpty + 1), null, true);
            if ($nextNonEmpty >= $endOfStatement) {
                break;
            }

            $contentLC = \strtolower($tokens[$nextNonEmpty]['content']);
            if ($contentLC === 'function' || $contentLC === 'const') {
                $nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, ($nextNonEmpty + 1), null, true);
            }

            // Skip past comma's and such.
            if (isset(Collections::namespacedNameTokens()[$tokens[$nextNonEmpty]['code']]) === false) {
                continue;
            }

            $nextNonEmpty = $this->analyseName($phpcsFile, $nextNonEmpty);

        } while ($nextNonEmpty < $endOfStatement);

        return $endOfStatement;
    }

    /**
     * Check whether the first part of a name tokenized according to the PHP 8 tokenization
     * could be a keyword and should have space between it and the actual name.
     *
     * @since 10.0.0
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    protected function splitAndExamineQualifiedNameForKeywords(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $parts  = \explode('\\', $tokens[$stackPtr]['content'], 2);

        $this->checkSpaceBefore($phpcsFile, $parts[0], $stackPtr);
    }

    /**
     * Make sure that there is whitespace before the namespace separator if the preceeding
     * token is a keyword.
     *
     * @since 10.0.0
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile     The file being scanned.
     * @param string                      $contentBefore The content of the "token before" which could
     *                                                   potentially be a keyword.
     * @param int                         $reportPtr     The position of the token to report an error on.
     *                                                   This must be a "real" token, not emulated.
     *
     * @return void
     */
    protected function checkSpaceBefore(File $phpcsFile, $contentBefore, $reportPtr)
    {
        $contentLC = \strtolower($contentBefore);
        $contentLC = \preg_replace('`\s{2,}`', ' ', $contentLC);

        if (isset($this->keywords[$contentLC]) === false) {
            return;
        }

        $error = 'There must be at least one space between a "%s" keyword and a namespaced name since PHP 8.0.';
        $data  = [$contentLC];
        $phpcsFile->addError($error, $reportPtr, 'SpaceRequired', $data);
    }

    /**
     * Verify that there is no whitespace or comments within a (namespaced) name.
     *
     * @since 10.0.0
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $start     The position of the first token in name.
     * @param int                         $end       Optional. The position to stop the name search at.
     *                                               This token should *not* be part of the name.
     *                                               Setting this parameter will force the check to
     *                                               ignore reserved keywords.
     *                                               Used to accommodate namespace declarations and
     *                                               group use statement syntax.
     *                                               Defaults to null.
     *
     * @return int Integer stack pointer to skip forward to.
     */
    protected function analyseName(File $phpcsFile, $start, $end = null)
    {
        if ($end === null) {
            $end      = $phpcsFile->numTokens;
            $fixedEnd = false;
        } else {
            $fixedEnd = true;
        }

        $tokens       = $phpcsFile->getTokens();
        $nameEnd      = $start;
        $firstInvalid = null;

        for ($i = $start; $i < $end; $i++) {
            if (isset(Tokens::$emptyTokens[$tokens[$i]['code']]) === true) {
                if (isset($firstInvalid) === false) {
                    $firstInvalid = $i;
                }
                continue;
            }

            if (isset(Collections::namespacedNameTokens()[$tokens[$i]['code']]) === true) {
                $nameEnd = $i;
                continue;
            }

            if ($fixedEnd === true
                && NamingConventions::isValidIdentifierName($tokens[$i]['content']) === true
            ) {
                $nameEnd = $i;
                continue;
            }

            /*
             * If we reach this point, we've reached a token which doesn't belong to the name.
             * Break even when there is supposed to be "fixed" end, as in that case, a
             * reserved keyword will have been used, but not in a "name" context.
             */
            break;
        }

        if (isset($firstInvalid) === false) {
            // No whitespace or comments found at all.
            return ($nameEnd + 1);
        }

        if ($firstInvalid > $nameEnd) {
            // Whitespace or comment directly after the last token in the name. Ignore.
            return ($nameEnd + 1);
        }

        $error = 'Whitespace or comments are not allowed within namespaced names since PHP 8.0. Found: %s';
        $data  = [GetTokensAsString::compact($phpcsFile, $start, $nameEnd, false)];
        $phpcsFile->addError($error, $firstInvalid, 'SpaceNotAllowed', $data);

        // Prevent throwing multiple errors for one name.
        return ($nameEnd + 1);
    }
}
