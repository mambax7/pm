<?php
// tests/bootstrap.php

// Define the path to the XOOPS mainfile.php.
// The user will need to adjust this path to their XOOPS installation.
if (defined('XOOPS_ROOT_PATH')) {
    require_once XOOPS_ROOT_PATH . '/mainfile.php';
} else {
    // A fallback for when the test is run from the module's directory
    // This assumes the module is in htdocs/modules/pm
    $xoops_root_path = dirname(__DIR__, 3);
    if (file_exists($xoops_root_path . '/mainfile.php')) {
        require_once $xoops_root_path . '/mainfile.php';
    } else {
        // If the path is defined in phpunit.xml, it will be used.
        // If not, we have to guess.
        if (getenv('XOOPS_ROOT_PATH')) {
            $xoops_root_path = getenv('XOOPS_ROOT_PATH');
        } else {
            // Make a wild guess
            $xoops_root_path = '/path/to/xoops/htdocs';
        }
        if (!defined('XOOPS_ROOT_PATH')) {
            define('XOOPS_ROOT_PATH', $xoops_root_path);
        }
        if (file_exists(XOOPS_ROOT_PATH . '/mainfile.php')) {
            require_once XOOPS_ROOT_PATH . '/mainfile.php';
        } else {
            echo "Unable to find mainfile.php. Please define XOOPS_ROOT_PATH in your phpunit.xml.\n";
            exit(1);
        }
    }
}

// Include the Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load the module's language files
\Xmf\Language::load('main', 'pm');
\Xmf\Language::load('admin', 'pm');
\Xmf\Language::load('modinfo', 'pm');

echo "XOOPS Environment bootstrapped for testing.\n";
