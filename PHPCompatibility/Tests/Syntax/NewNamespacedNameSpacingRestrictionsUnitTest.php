<?php
/**
 * PHPCompatibility, an external standard for PHP_CodeSniffer.
 *
 * @package   PHPCompatibility
 * @copyright 2012-2020 PHPCompatibility Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCompatibility/PHPCompatibility
 */

namespace PHPCompatibility\Tests\Syntax;

use PHPCompatibility\Tests\BaseSniffTest;
use PHPCSUtils\TestUtils\UtilityMethodTestCase;

/**
 * Test the NewNamespacedNameSpacingRestrictions sniff.
 *
 * @group newNamespacedNameSpacingRestrictions
 * @group syntax
 *
 * @covers \PHPCompatibility\Sniffs\Syntax\NewNamespacedNameSpacingRestrictionsSniff
 *
 * @since 10.0.0
 */
class NewNamespacedNameSpacingRestrictionsUnitTest extends BaseSniffTest
{

    /**
     * Verify that violations against the new namespaced name spacing rule for spaces and comments
     * within names are detected correctly.
     *
     * @dataProvider dataSpaceNotAllowed
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testSpaceNotAllowed($line)
    {
        $file = $this->sniffFile(__FILE__, '8.0');
        $this->assertError($file, $line, 'Whitespace or comments are not allowed within namespaced names since PHP 8.0.');
    }

    /**
     * Data provider.
     *
     * @see testSpaceNotAllowed()
     *
     * @return array
     */
    public function dataSpaceNotAllowed()
    {
        $data = [
            [101],
            [104],
            [106],
            [108],
            [109],
            [110],
            [112],
            [113],
            [118],
            [120],
            [121],
            [123],
            [124],
            [126],
            [128],
            [129],
            [130],
            [134],
            [135],
            [136],
            [137],
            [140],
            [141],
            [142],
            [144],
            [146],
            [147],
            [149],
            [150],
            [151],
            [155],
            [156],
            [157], // Error x2.
            [158],
            [159],
            [160],
            [161],
            [162],
            [163],
            [165],
            [166],
            [167],
            [168],
            [170],
            [173],
            [174],
            [175],
            [180],
            [181],
            [182],
            [194],
            [196],
            [201], // Error x2.
            [204],
            [207],
            [233],
        ];

        if (UtilityMethodTestCase::usesPhp8NameTokens() === true) {
            $data[] = [220];
            $data[] = [221];
        }

        return $data;
    }


    /**
     * Verify that violations against the new namespaced name spacing rules for required space (or comment)
     * between a keyword and a namespace separator are detected correctly.
     *
     * @dataProvider dataSpaceRequired
     *
     * @param int    $line    The line number.
     * @param string $keyword The keyword identified.
     *
     * @return void
     */
    public function testSpaceRequired($line, $keyword)
    {
        $file = $this->sniffFile(__FILE__, '8.0');
        $this->assertError(
            $file,
            $line,
            'There must be at least one space between a "' . $keyword . '" keyword and a namespaced name since PHP 8.0.'
        );
    }

    /**
     * Data provider.
     *
     * @see testSpaceRequired()
     *
     * @return array
     */
    public function dataSpaceRequired()
    {
        $data = [
            [115, 'function'],
            [116, 'const'],
            [150, 'insteadof'],
            [222, 'instanceof'],
            [225, 'yield'],
            [227, 'yield from'],
            [233, 'new'],
        ];

        /*
         * These tests will throw the "correct" error in combination with PHP 5/7 tokenization.
         * With PHP 8 identifier name tokenization, they will still throw an error, but due to
         * the different tokens, it will be the `SpaceNotAllowed` one.
         * As this means the user still *will* be alerted, fixing this is not a high priority (and pretty hard to do).
         */
        if (UtilityMethodTestCase::usesPhp8NameTokens() === false) {
            $data[] = [220, 'implements'];
            $data[] = [221, 'extends'];
        }

        return $data;
    }


    /**
     * Test the sniff doesn't throw false positives for valid code.
     *
     * @dataProvider dataNoFalsePositives
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testNoFalsePositives($line)
    {
        $file = $this->sniffFile(__FILE__, '8.0');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testNoFalsePositives()
     *
     * @return array
     */
    public function dataNoFalsePositives()
    {
        $data = [];
        for ($line = 1; $line <= 96; $line++) {
            $data[] = [$line];
        }

        $data[] = [188];
        $data[] = [216];
        $data[] = [239];
        $data[] = [240];
        $data[] = [242];

        return $data;
    }


    /**
     * Verify no notices are thrown at all.
     *
     * @return void
     */
    public function testNoViolationsInFileOnValidVersion()
    {
        $file = $this->sniffFile(__FILE__, '7.4');
        $this->assertNoViolation($file);
    }
}
