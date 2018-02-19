<?php
date_default_timezone_set("Europe/Amsterdam");

$path = \Parable\DI\Container::get(\Parable\Filesystem\Path::class);
$path->setBaseDir(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..'));

/*
 * And load and register the framework's autoloader
 */
$autoloader = \Parable\DI\Container::get(\Parable\Framework\Autoloader::class);
$autoloader->addLocation($path->getDir("tests/TestClasses"));
$autoloader->register();
