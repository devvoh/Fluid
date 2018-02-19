<?php

namespace Parable\Tests\TestClasses\Config;

class TestNoRouting implements
    \Parable\Framework\Interfaces\Config
{
    public function get()
    {
        return [
            "parable" => [
            ],
        ];
    }
}
