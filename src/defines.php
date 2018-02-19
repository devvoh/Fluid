<?php
// Here we define some constants Parable has a use of
if (!defined("DS")) {
    define("DS", DIRECTORY_SEPARATOR);
}
if (!defined("BASEDIR")) {
    define("BASEDIR", realpath(__DIR__ . DS . ".." . DS . ".." . DS . ".." . DS . ".."));
}
