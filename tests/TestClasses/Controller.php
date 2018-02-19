<?php

namespace Parable\Tests\TestClasses;

class Controller
{
    public function index()
    {
    }

    public function simple()
    {
        return "simple action";
    }

    public function complex($id, $name)
    {
        return [$id, $name];
    }

    public static function staticIndex()
    {
        return "static index here!";
    }
}
