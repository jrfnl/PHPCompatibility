<?php

/**
 * Install functions
 *
 * Used through auto-scripts from Composer and vendor/bin/phpcompat_(en|dis)able/update
 *
 */

class PHPCompatibility_Scripts_Install
{

    /**
     * Files which need to be copied over and live in the root directory of this project.
     *
     * @var array
     */
    public static $root_dir_files = array(
        'ruleset.xml',
        'Sniff.php',
    );


    public static function enable()
    {
        echo "(Re-)Enabling PHPCompatibility\n";
        self::make_copy();
    }


    public static function disable()
    {
        self::remove_copy();
    }


    public static function update()
    {
        self::disable();
        self::enable();
    }


    protected static function make_copy()
    {
        $srcDir = dirname(__DIR__);
        $copy = dirname(__DIR__).DIRECTORY_SEPARATOR.'PHPCompatibility';

        if ( file_exists ($copy)) {
            echo "Copy workaround is already in place\n";
            return;
        }

        if (mkdir($copy) === true) {
            foreach (self::$root_dir_files as $filename) {
                copy($srcDir.DIRECTORY_SEPARATOR.$filename, $copy.DIRECTORY_SEPARATOR.$filename);
            }

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $copy = str_replace('/', DIRECTORY_SEPARATOR, $copy);
                $srcDir = str_replace('/', DIRECTORY_SEPARATOR, $srcDir);
                passthru('xcopy "'.$srcDir .DIRECTORY_SEPARATOR.'Sniffs" "'.$copy.DIRECTORY_SEPARATOR.'Sniffs" /S /E /I');
            } else {
                passthru('cp -r "'.$srcDir .DIRECTORY_SEPARATOR.'Sniffs" "'.$copy.DIRECTORY_SEPARATOR.'Sniffs"');
            }
            echo "Created copy workaround\n";
        } else {
            echo "Failed to create the $copy directory\n";
        }
    }


    protected static function remove_copy()
    {
        $copy = dirname(__DIR__).DIRECTORY_SEPARATOR.'PHPCompatibility';

        if ( ! file_exists ($copy)) {
            echo "No copy workaround to remove\n";
            return;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $copy = str_replace('/', DIRECTORY_SEPARATOR, $copy);
            passthru('rmdir /S /Q "'.$copy.'"');
        } else {
            passthru('rm -rf "'.$copy.'"');
        }
        echo "Copy workaround removed\n";
    }


}
