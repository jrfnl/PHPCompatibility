ABOUT THESE FILES
===============================

The files in this directory are intended for Composer / Composer users.

The `Install.php` and `Register.php` files are automatically used by Composer in a stand-alone installation of this library to work-around some path issues.

For an installation which requires this library as one of it's dependencies, it is suggested to add these scripts to your projects `composer.json` file. For up-to-date instructions on how to do so, please refer to the main [README](https://github.com/wimg/PHPCompatibility/blob/master/README.md) of this project.

If you prefer not to do so, you will need to run these commands manually.
The non-suffixed files - `phpcompat_enable`, `phpcompat_update` and `phpcompat_disable` - will be symlinked by Composer to the `/vendor/bin/` directory for ease of use.
For up-to-date instructions on how and when to manually run these script, please refer to the main [README](https://github.com/wimg/PHPCompatibility/blob/master/README.md) of this project.
