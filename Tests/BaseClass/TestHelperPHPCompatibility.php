<?php
/**
 * Test Helper file.
 *
 * @package PHPCompatibility
 */

if (class_exists('PHPCompatibility_Sniff', true) === false) {
    $pathComposerInstall = dirname(__FILE__) . '/../../PHPCompatibility/Sniff.php';
    $pathPearInstall     = dirname(__FILE__) . '/../../Sniff.php';

    if (file_exists($pathComposerInstall)) {
        require_once $pathComposerInstall;
    }
    else if (file_exists($pathPearInstall)) {
        require_once $pathPearInstall;
    }
    else {
        throw new Exception('File containing the class PHPCompatibility_Sniff cannot be found.');
    }
}

/**
 * Helper class to facilitate testing of the methods within the abstract PHPCompatibility_Sniff class.
 *
 * @uses BaseSniffTest
 * @package PHPCompatibility
 */
class BaseClass_TestHelperPHPCompatibility extends PHPCompatibility_Sniff {
	public function register() {}
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {}
}
