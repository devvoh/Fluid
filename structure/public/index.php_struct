<?php
$autoloaderPath = realpath(__DIR__ . "/../vendor/autoload.php");

if (!$autoloaderPath) {
    die("<b>ERROR</b>: You need to run <code>composer install</code> before Parable will work.");
}

require_once $autoloaderPath;

$app = \Parable\DI\Container::create(\Parable\Framework\App::class);
$app->run();
