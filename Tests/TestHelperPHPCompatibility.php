<?php
/**
 * Test Helper file.
 *
 * @package PHPCompatibility
 */

if (class_exists('PHPCompatibility_Sniff', true) === false) {
    require_once dirname( dirname(__FILE__) ) . '/PHPCompatibility/Sniff.php';
}

/**
 * Helper class to facilitate testing of the methods within the abstract PHPCompatibility_Sniff class.
 *
 * @uses BaseSniffTest
 * @package PHPCompatibility
 */
class TestHelperPHPCompatibility extends PHPCompatibility_Sniff {
	public function register() {}
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {}
}
