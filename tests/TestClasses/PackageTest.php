<?php

namespace Parable\Tests\TestClasses;

class PackageTest implements \Parable\Framework\Package\PackageInterface
{
    public function getCommands()
    {
        return [
            Command::class
        ];
    }

    public function getInits()
    {
        return [
            Init\TestEcho::class
        ];
    }
}
