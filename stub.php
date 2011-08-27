#!/usr/bin/env php
<?php
/**
 * If your package does special stuff in phar format, use this file.  Remove if
 * no phar format is ever generated
 * More information: http://pear.php.net/manual/en/pyrus.commands.package.php#pyrus.commands.package.stub
 */
if (version_compare(phpversion(), '5.3.1', '<')) {
    if (substr(phpversion(), 0, 5) != '5.3.1') {
        // this small hack is because of running RCs of 5.3.1
        echo "Diggin_Scraper requires PHP 5.3.1 or newer.
";
        exit -1;
    }
}
foreach (array('phar', 'spl', 'pcre', 'simplexml') as $ext) {
    if (!extension_loaded($ext)) {
        echo 'Extension ', $ext, " is required
";
        exit -1;
    }
}
try {
    Phar::mapPhar();
} catch (Exception $e) {
    echo "Cannot process Diggin_Scraper phar:
";
    echo $e->getMessage(), "
";
    exit -1;
}
function Diggin_Scraper_autoload($class)
{
    $class = str_replace(array('_', '\\'), '/', $class);
    if (file_exists('phar://' . __FILE__ . '/Diggin_Scraper-0.4.0/php/' . $class . '.php')) {
        include 'phar://' . __FILE__ . '/Diggin_Scraper-0.4.0/php/' . $class . '.php';
    }
}
spl_autoload_register("Diggin_Scraper_autoload");
$phar = new Phar(__FILE__);
$sig = $phar->getSignature();
define('Diggin_Scraper_SIG', $sig['hash']);
define('Diggin_Scraper_SIGTYPE', $sig['hash_type']);

// your package-specific stuff here, for instance, here is what Pyrus does:

/**
 * $frontend = new \PEAR2\Pyrus\ScriptFrontend\Commands;
 * @array_shift($_SERVER['argv']);
 * $frontend->run($_SERVER['argv']);
 */
__HALT_COMPILER();
