<?php

namespace Parable\Tests\TestClasses\Config;

class Test implements
    \Parable\Framework\Interfaces\Config
{
    public function get()
    {
        return [
            "parable" => [
                "app" => [
                    "title" => "Parable",
                    "homedir" => "public",
                ],
                "session" => [
                    "auto-enable" => true,
                ],
                "database" => [
                    "type" => \Parable\ORM\Database::TYPE_MYSQL,
                    "location" => "localhost",
                    "username" => "username",
                    "password" => "password",
                    "database" => "database",
                ],
                "configs" => [
                    \Parable\Tests\TestClasses\Config\Custom::class
                ],
                "commands" => [
                    \Parable\Tests\TestClasses\Command\Test::class
                ],
                "inits" => [
                    \Parable\Tests\TestClasses\Init\Test::class
                ],
                "routes" => [
                    \Routing\App::class,
                ],
            ],
        ];
    }
}
