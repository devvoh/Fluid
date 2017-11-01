<?php
date_default_timezone_set("Europe/Amsterdam");

define('DS', DIRECTORY_SEPARATOR);
define('BASEDIR', realpath(__DIR__ . DS . '..'));

/** @var \Parable\Filesystem\Path $path */
$path = \Parable\DI\Container::get(\Parable\Filesystem\Path::class);
$path->setBaseDir(BASEDIR);

/*
 * And load and register the framework's autoloader
 */
/**@var \Parable\Framework\Autoloader $autoloader */
$autoloader = \Parable\DI\Container::get(\Parable\Framework\Autoloader::class);
$autoloader->addLocation($path->getDir("tests/TestClasses"));
$autoloader->register();
