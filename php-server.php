<?php
$baseDir = __DIR__ . "/../../../public";
$baseDir = realpath($baseDir);
$path    = $baseDir . $_SERVER["REQUEST_URI"];

if (!is_dir($path) && file_exists($path)) {
    $fileinfo = new SplFileObject($path);

    $mimetype = mime_content_type($path);

    if ($mimetype == "text/plain") {
        switch ($fileinfo->getExtension()) {
            case "js":
            case "json":
                $mimetype = "application/javascript";
                break;
            case "css":
                $mimetype = "text/css";
                break;
        }
    }
    header("Content-type: {$mimetype}");
    echo trim(file_get_contents($path));
    include($path);
    return;
}

ini_set("date.timezone", "Europe/Amsterdam");

// We set this value so we can detect this further on
$_SERVER["PHP_SERVER"] = true;

// The built-in webserver does not set these in a way Parable expects it (htaccess redirect) so we do it ourselves
if (file_exists("public/index.php")) {
    $correct = "./public/index.php";
} else {
    $correct = "./index.php";
}
$_SERVER["SCRIPT_FILENAME"] = str_replace("php-server.php", $correct, $_SERVER["SCRIPT_FILENAME"]);
$_SERVER["SCRIPT_NAME"] = str_replace(__DIR__, "", $_SERVER["SCRIPT_FILENAME"]);

// Normally the redirect is to index.php?url=REQUEST_URI, but obviously that's not the case here
$_GET["url"] = $_SERVER["REQUEST_URI"];

include($_SERVER["SCRIPT_NAME"]);
