<?php

namespace Parable\Framework\Package;

class PackageManager
{
    /** @var PackageInterface[] */
    protected $packages = [];

    /**
     * Add a package by name.
     *
     * @param string $packageName
     *
     * @return $this
     */
    public function addPackage($packageName)
    {
        $this->packages[] = $packageName;
        return $this;
    }

    /**
     * Load all Commands from package.
     *
     * @param PackageInterface $package
     *
     * @return $this
     * @throws \Parable\DI\Exception
     */
    protected function loadCommands(PackageInterface $package)
    {
        if (!$package->getCommands() || APP_CONTEXT !== 'console') {
            return $this;
        }

        $commandLoader = \Parable\DI\Container::create(\Parable\Framework\Loader\CommandLoader::class);
        $commandLoader->load($package->getCommands());
        return $this;
    }

    /**
     * Register all packages with Parable.
     *
     * @return $this
     * @throws \Parable\DI\Exception
     */
    public function registerPackages()
    {
        foreach ($this->packages as $packageName) {
            $package = \Parable\DI\Container::create($packageName);
            $this->loadCommands($package);
            $this->loadInits($package);
        }
        return $this;
    }

    /**
     * Load all Inits from package.
     *
     * @param PackageInterface $package
     *
     * @return $this
     * @throws \Parable\DI\Exception
     */
    protected function loadInits(PackageInterface $package)
    {
        if (!$package->getInits()) {
            return $this;
        }

        $initLoader = \Parable\DI\Container::create(\Parable\Framework\Loader\InitLoader::class);
        $initLoader->load($package->getInits());
        return $this;
    }
}
