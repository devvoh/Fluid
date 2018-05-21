<?php

namespace Parable\Tests\TestClasses;

class PackageTestEmpty implements \Parable\Framework\Package\PackageInterface
{
    public function getCommands()
    {
        return [];
    }

    public function getInits()
    {
        return [];
    }
}
