<?php

namespace Parable\Tests\TestClasses\Config;

class Invalid implements
    \Parable\Framework\Interfaces\Config
{
    public function get()
    {
        return;
    }
}
