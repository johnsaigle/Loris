#!/usr/bin/env php
<?php declare(strict_types=1);
/**
 * PHP Version 7
 *
 * @category Main
 * @package  Loris
 * @author   John Saigle <john.saigle@mcin.ca>
 * @license  http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 * @link     https://www.github.com/aces/Loris-Trunk/
 */
require_once __DIR__ . '/cli_helper.class.inc';

define('MINIMUM_PHP_VERSION', 7.3);
define('MINIMUM_APACHE_VERSION', 2.4);
define('MINIMUM_MYSQL_VERSION', 5.7);
define('MINIMUM_MARIADB_VERSION', 10.3);

$helper = new CLI_Helper();
$helper->enableLogging(basename($argv[0]));

$helper->printLine('Checking PHP version....');
// Make sure the right PHP version is used.
evaluateVersionRequirement('PHP', PHP_VERSION, MINIMUM_PHP_VERSION);

$helper->printLine('Checking Apache version....');
if (function_exists('apache_get_version')) {
    // Output format: Apache/1.3.29 (Unix) PHP/4.3.4
    // Parse this to get just the APACHE version number.
    $apacheVersion = substr(
        // Extract 'Apache/x.x.x' from output of apache_get_version()
        explode(' ', apache_get_version())[0],
        0,
        len('Apache/')
    );
    evaluateVersionRequirement('Apache', $apacheVersion, MINIMUM_APACHE_VERSION);

} else {
    $helper->printWarning('Not running on an Apache server.');
}

$helper->printLine('Checking database version');
// This variable should contain "MySQL" or "MariaDB"
$architecture = \NDB_Factory::singleton()->database()->getArchitecture();
// Numeric version information
$version = \NDB_Factory::singleton()->database()->getVersion();

if (strpos(strtolower($architecture), 'mysql') !== false) {
    evaluateVersionRequirement('MySQL', $version, MINIMUM_MYSQL_VERSION);
} else if (strpos(strtolower($architecture), 'mariadb') !== false) {
    evaluateVersionRequirement('MariaDB', $version, MINIMUM_MARIADB_VERSION);
} else {
    $helper->printError('Neither MariaDB nor MySQL installation detected');
}

// Check "web paths". This is a data type found in the ConfigSettings table.
// Settings with this Data Type are expected to exist on the file system and
// should be writable by the current user.
$username = $helper->getUsername();
$helper->printLine("Checking web path settings...");
$query = <<<QUERY
SELECT 
cs.Name, c.Value as Path
FROM Config c 
LEFT JOIN ConfigSettings cs ON (c.ConfigID = cs.ID) 
JOIN ConfigSettings csp ON (c.ConfigID = csp.ID) 
WHERE csp.DataType='web_path';
QUERY;
$result = $DB->pselect($query, []);

foreach ($result as $setting) {
    $name = $setting['Name'];
    $path = $setting['Path'];

    // If the path is an empty string, print a warning.
    if ($path === "") {
        $helper->printWarning(
            "Setting `$name` is has no value associated with it. This may "
            . " affect the functionality of LORIS."
        );
        continue;
    }
    // If the path does not exist on the system, print a warning.
    // It is not required that all paths are set as some projects may not use
    // certain paths.
    if (!is_dir($path)) {
        $helper->printWarning(
            "Setting `$name` has the value `$path` which is not a directory "
            . " on the filesystem."
        );
        continue;
    }

    // If the path exists but is not writable, print an error. All web paths
    // should be writable by Apache if they are configured.
    if (!is_writeable($path)) {
        $helper->printError(
            "Setting `$name` has the value `$path` which is not writable "
            . "by user `$username`."
        );
    }
}

/**
 * Evaluate version requirement. Print result.
 */
function evaluateVersionRequirement(
    string $software, 
    $versionInstalled, 
    $versionRequired
) {
    global $helper;
    // Make sure the right Apache version is used.
    $versionRequired >= $versionInstalled ?
        $helper->printSuccess(
            sprintf(
                "$software version requirment met (found: %s. required: %s)",
                $versionInstalled,
                $versionRequired
            )
        )
        : $helper->printError(
            sprintf(
                "$software minimum version not met (found: %s. required: %s)",
                $versionInstalled,
                $versionRequired
            )
        );
}

exit;
