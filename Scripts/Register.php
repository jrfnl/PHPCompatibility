<?php

/**
 * Install functions
 *
 * Used through auto-scripts from Composer and vendor/bin/phpcompat_(en|dis)able/update
 *
 */

class PHPCompatibility_Scripts_Register
{


    public static function update()
    {
        self::register_in_cs();
    }


    public static function disable()
    {
        self::unregister_from_cs();
    }


    protected static function register_in_cs()
    {
        $installed_paths = self::get_installed_path();
        $target_path     = dirname(__DIR__);
        if (in_array($target_path, $installed_paths, true)) {
            echo "Our path is already registered in PHP CodeSniffer\n";
        } else {
            array_push($installed_paths, $target_path);
            self::set_installed_path($installed_paths);
            echo "Registered our path in PHP CodeSniffer\n";
        }
    }


    protected static function unregister_from_cs()
    {
        $installed_paths = self::get_installed_path();
        if (! in_array(__DIR__, $installed_paths)) {
            echo "Our path is not registered in PHP CodeSniffer\n";
        } else {
            $installed_paths = array_filter($installed_paths, function ($v) {
                return $v != __DIR__;
            });
            self::set_installed_path($installed_paths);
            echo "Unregistered our path in PHP CodeSniffer\n";
        }
    }


    protected static function get_installed_path()
    {
        $installed_paths = PHP_CodeSniffer::getConfigData('installed_paths');
        if ( $installed_paths === null || strlen($installed_paths) === 0 ) {
            // Because: explode(',' , NULL) == array('')
            // and we assert no data is empty array
            return array();
        }
        return explode(',', $installed_paths);
    }


    protected static function set_installed_path($array)
    {
        if(count($array) === 0) {
            PHP_CodeSniffer::setConfigData('installed_paths', null);
        } else {
            PHP_CodeSniffer::setConfigData('installed_paths', implode(',', $array));
        }
    }


}
