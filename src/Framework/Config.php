<?php

namespace Parable\Framework;

class Config extends \Parable\GetSet\Base
{
    /** @var string */
    protected $resource = 'parable_config';

    /** @var bool */
    protected $useLocalResource = true;

    /** @var string */
    protected $mainConfigClass = \Config\App::class;

    /** @var \Parable\Filesystem\Path */
    protected $path;

    public function __construct(
        \Parable\Filesystem\Path $path
    ) {
        $this->path = $path;
    }

    /**
     * @param string $className
     *
     * @return $this
     * @throws Exception
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
     * @return $this
     */
    public function load()
    {
        try {
            $this->addConfig(\Parable\DI\Container::get($this->mainConfigClass));
        } catch (\Exception $e) {
            // We don't throw here since the file isn't required to exist, but we do stop here
            return $this;
        }

        if ($this->get("parable.configs")) {
            foreach ($this->get("parable.configs") as $configClass) {
                $this->addConfig(\Parable\DI\Container::get($configClass));
            }
        }

        return $this;
    }

    /**
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
