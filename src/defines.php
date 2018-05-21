<?php
/**
 * DIRECTORY_SEPARATOR is just too long -- shorten it!
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Relative paths are hell. Make sure we can work from a reliable base directory.
 */
if (!defined('BASEDIR')) {
    define('BASEDIR', realpath(__DIR__ . DS . '..' . DS . '..' . DS . '..' . DS . '..'));
}

/**
 * We want to be able to know whether we're in web or console context.
 */
if (!defined('APP_CONTEXT')) {
    define('APP_CONTEXT', (PHP_SAPI === 'cli' ? 'console' : 'web'));
}

/**
 * We want to allow packages to register themselves with Parable.
 */
if (!function_exists('register_parable_package')) {
    function register_parable_package($packageName)
    {
        \Parable\DI\Container::get(\Parable\Framework\Package\PackageManager::class)->addPackage($packageName);
    }
}
