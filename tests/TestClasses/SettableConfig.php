<?php

namespace Parable\Tests\TestClasses;

class SettableConfig implements
    \Parable\Framework\Interfaces\Config
{
    protected $config = [];

    /**
     * @return array
     */
    public function get()
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function set(array $config)
    {
        $this->config = $config;
        return $this;
    }
}
