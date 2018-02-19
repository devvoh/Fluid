<?php

namespace Parable\Framework\Routing;

abstract class AbstractRouting
{
    /** @var \Parable\Framework\App */
    protected $app;

    public function __construct(\Parable\Framework\App $app)
    {
        $this->app = $app;
    }

    /**
     * Load all routes onto the App.
     */
    abstract public function load();
}
