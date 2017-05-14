PHP Compatibility Coding Standard for PHP_CodeSniffer
=====================================================
[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=wimg&url=https://github.com/wimg/PHPCompatibility&title=PHPCompatibility&language=&tags=github&category=software)
[![Build Status](https://travis-ci.org/wimg/PHPCompatibility.png?branch=master)](https://travis-ci.org/wimg/PHPCompatibility)
[![Coverage Status](https://coveralls.io/repos/github/wimg/PHPCompatibility/badge.svg?branch=master)](https://coveralls.io/github/wimg/PHPCompatibility?branch=master)
[![Latest Stable Version](https://poser.pugx.org/wimg/php-compatibility/v/stable.png)](https://packagist.org/packages/wimg/php-compatibility)
[![Latest Unstable Version](https://poser.pugx.org/wimg/php-compatibility/v/unstable.png)](https://packagist.org/packages/wimg/php-compatibility)
[![License](https://poser.pugx.org/wimg/php-compatibility/license.png)](https://packagist.org/packages/wimg/php-compatibility)

This is a set of sniffs for [PHP_CodeSniffer](http://pear.php.net/PHP_CodeSniffer) that checks for PHP version compatibility.
It will allow you to analyse your code for compatibility with higher and lower versions of PHP. 


PHP Version Support
-------

The project aims to cover all PHP compatibility changes introduced since PHP 5.0 up to the latest PHP release.  This is an ongoing process and coverage is not yet 100% (if, indeed, it ever could be).  Progress is tracked on [our Github issue tracker](https://github.com/wimg/PHPCompatibility/issues).

Pull requests that check for compatibility issues in PHP4 code - in particular between PHP 4 and PHP 5.0 - are very welcome as there are still situations where people need help upgrading legacy systems. However, coverage for changes introduced before PHP 5.1 will remain patchy as sniffs for this are not actively being developed at this time.

Requirements
-------

The sniffs are designed to give the same results regardless of which PHP version you are using to run CodeSniffer.  You should get reasonably consistent results independently of the PHP version used in your test environment, though for the best results it is recommended to run the sniffs on PHP 5.3 or higher.

PHP CodeSniffer 1.5.1 is required for 90% of the sniffs, PHPCS 2.6 or later is required for full support, notices may be thrown on older versions.

**_The PHPCompatibility standard is currently not compatible with PHPCS 3.0, though the [intention is to fix this](https://github.com/wimg/PHPCompatibility/issues/367) in the near future._**

Thank you
---------
Thanks to all contributors for their valuable contributions.

[![WPEngine](https://cu.be/img/wpengine.png)](https://wpengine.com)

Thanks to [WP Engine](https://wpengine.com) for their support on the PHP 7.0 sniffs.


Important Upgrade Notice
--------
As of version 7.1.5, the installation instructions have changed. For most users, this means they will have to run a one-time-only extra command or make a change to their Composer configuration.

Please read the changelog for version [7.1.5](https://github.com/wimg/PHPCompatibility/releases/tag/7.1.5) for more details.


Installation in Composer project (method 1)
-------------------------------------------

* Add the following lines to the `require-dev` section of your `composer.json` file.
    ```json
    "require-dev": {
        "squizlabs/php_codesniffer": "^2.0",
        "wimg/php-compatibility": "*"
    },
    "prefer-stable" : true
```
* Next, PHPCS has to be informed of the location of the standard.
    - If you use just one external PHPCS standard, you can add the following to your `composer.json` file to automatically run the necessary command:
        ```json
        "scripts": {
            "post-install-cmd": "\"vendor/bin/phpcs\" --config-set installed_paths /vendor/wimg/php-compatibility",
            "post-update-cmd" : "\"vendor/bin/phpcs\" --config-set installed_paths /vendor/wimg/php-compatibility"
        }
        ```
    - Alternatively - and **_strongly recommended_** if you use more than one external PHPCS standard - you can use any of the following Composer plugins to handle this for you.
	   Add the Composer plugin you prefer to the `require-dev` section of your `composer.json` file.
            * [DealerDirect/phpcodesniffer-composer-installer](https://github.com/DealerDirect/phpcodesniffer-composer-installer)
            * [higidi/composer-phpcodesniffer-standards-plugin](https://github.com/higidi/composer-phpcodesniffer-standards-plugin)
            * [SimplyAdmire/ComposerPlugins](https://github.com/SimplyAdmire/ComposerPlugins)
    - As a last alternative in case you use a custom ruleset, _and only if you use PHPCS version 2.6.0 or higher_, you can tell PHP_CodeSniffer the path to the PHPCompatibility standard by adding the following snippet to your custom ruleset:
        ```xml
        <config name="installed_paths" value="/vendor/wimg/php-compatibility" />
        ```
* Run `composer update --lock` to install both PHP CodeSniffer, the PHPCompatibility coding standard and - optionally - the Composer plugin.
* Verify that the PHPCompatibility standard is registered correctly by running `phpcs -i`. PHPCompatibility should be listed as one of the available standards.
* Use the coding standard with `./vendor/bin/phpcs --standard=PHPCompatibility`


Installation via a git check-out to an arbitrary directory (method 2)
-----------------------

* Install the latest `2.x` version of [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) via [your preferred method](https://github.com/squizlabs/PHP_CodeSniffer#installation) (Composer, [PEAR](http://pear.php.net/PHP_CodeSniffer), Phar file, Git checkout).
* Checkout the [latest PHPCompatibility release](https://github.com/wimg/PHPCompatibility/releases) into an arbitrary directory.
* Add the path to the directory in which you cloned the PHPCompatibility repo to the PHPCS configuration using the below command.
   ```bash
   phpcs --config-set installed_paths /path/to/PHPCompatibility
   ```
   I.e. if you cloned the `PHPCompatibility` repository to the `/my/custom/standards/PHPCompatibility` directory, you will need to add that directory to the PHPCS [`installed_paths` configuration variable](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Configuration-Options#setting-the-installed-standard-paths).

   **Pro-tip:** Alternatively, _and only if you use PHPCS version 2.6.0 or higher_, you can tell PHP_CodeSniffer the path to the PHPCompatibility standard by adding the following snippet to your custom ruleset:
   ```xml
   <config name="installed_paths" value="/path/to/PHPCompatibility" />
   ```
* Verify that the PHPCompatibility standard is registered correctly by running `phpcs -i`. PHPCompatibility should be listed as one of the available standards.
* Use the coding standard with `phpcs --standard=PHPCompatibility`


Sniffing your code for compatibility with specific PHP version
------------------------------
* You can specify which PHP version you want to test against by specifying `--runtime-set testVersion 5.5`.
* You can also specify a range of PHP versions that your code needs to support.  In this situation, compatibility issues that affect any of the PHP versions in that range will be reported:
`--runtime-set testVersion 5.3-5.5`.  You can omit one or other part of the range if you want to support everything above/below a particular version (e.g. `--runtime-set testVersion 7.0-` to support PHP 7 and above).

More information can be found on Wim Godden's [blog](http://techblog.wimgodden.be/tag/codesniffer).

Using a custom ruleset
------------------------------
Alternatively, you can add PHPCompatibility to a custom PHPCS ruleset.

```xml
<?xml version="1.0"?>
<ruleset name="Custom ruleset">
	<description>My rules for PHP_CodeSniffer</description>

	<!-- Run against the PHPCompatibility ruleset -->
	<rule ref="PHPCompatibility" />
	
	<!-- Run against a second ruleset -->
	<rule ref="PSR2" />

</ruleset>
```

You can also set the `testVersion` from within the ruleset:
```xml
	<config name="testVersion" value="5.3-5.5"/>
```

Other advanced options, such as changing the message type or severity of select sniffs, as described in the [PHPCS Annotated ruleset](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Annotated-ruleset.xml) wiki page are, of course, also supported.


#### PHPCompatibility specific options

At this moment, there is one sniff which has a property which can be set via the ruleset. More custom properties may become available in the future.

The `PHPCompatibility.PHP.RemovedExtensions` sniff checks for removed extensions based on the function prefix used for these extensions.
This might clash with userland functions using the same function prefix.

To whitelist userland functions, you can pass a comma-delimited list of function names to the sniff.
```xml
	<!-- Whitelist the mysql_to_rfc3339() and mysql_another_function() functions. -->
	<rule ref="PHPCompatibility.PHP.RemovedExtensions">
		<properties>
			<property name="functionWhitelist" type="array" value="mysql_to_rfc3339,mysql_another_function" />
		</properties>
	</rule>
```


License
-------
This code is released under the GNU Lesser General Public License (LGPL). For more information, visit http://www.gnu.org/copyleft/lesser.html
