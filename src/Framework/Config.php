<?php

namespace Parable\Framework;

class Config extends \Parable\GetSet\Base
{
    /** @var string */
    protected $resource = 'parable_config';

    /** @var bool */
    protected $useLocalResource = true;

    /** @var string */
    protected $mainConfigClass = '\Config\App';

    /** @var \Parable\Filesystem\Path */
    protected $path;

    public function __construct(
        \Parable\Filesystem\Path $path
    ) {
        $this->path = $path;
    }

    /**
     * Set the main config name to use.
     *
     * @param string $className
     *
     * @return $this
     * @throws \Parable\Framework\Exception
     */
    public function setMainConfigClassName($className)
    {
        if (!class_exists($className)) {
            throw new \Parable\Framework\Exception("Main Config class '{$className}' does not exist.");
        }
        $this->mainConfigClass = $className;
        return $this;
    }

    /**
     * Load the main config and load all its values. If there are any child configs defined under
     * "parable.configs", load all of those too.
     *
     * @return $this
     */
    public function load()
    {
        try {
            $this->addConfig(\Parable\DI\Container::get($this->mainConfigClass));
        } catch (\Exception $e) {
            return $this;
        }

        if ($this->get('parable.configs')) {
            foreach ($this->get('parable.configs') as $configClass) {
                $this->addConfig(\Parable\DI\Container::get($configClass));
            }
        }

        return $this;
    }

    /**
     * Add a config and load all of its values.
     *
     * @param \Parable\Framework\Interfaces\Config $config
     *
     * @return $this
     */
    public function addConfig(\Parable\Framework\Interfaces\Config $config)
    {
        $this->setMany($config->get());
        return $this;
    }
}
