<?php

namespace Parable\Tests\Components\Framework;

class Base extends \Parable\Tests\Components\ORM\Base
{
    /** @var \Parable\Framework\App */
    protected $app;

    protected function setUp()
    {
        parent::setUp();

        // Since many Framework components depend on \Parable\Http\Request,
        // which depends on some global values, we set them.
        $GLOBALS["_SERVER"];

        $GLOBALS['_SERVER'] = [
            "argv"           => [],
            "REQUEST_METHOD" => "GET",
            "REQUEST_SCHEME" => "http",
            "HTTP_HOST"      => "www.test.dev",
            "SCRIPT_NAME"    => "/test/public/index.php",
        ];

        $GLOBALS["_SESSION"] = [];
    }
}
