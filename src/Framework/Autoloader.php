<?php

namespace Parable\Framework;

class Autoloader
{
    /** @var array */
    protected $locations = [];

    /**
     * Register the autoloader.
     *
     * @return $this
     */
    public function register()
    {
        spl_autoload_register([$this, 'load']);
        return $this;
    }

    /**
     * Add a location to the stack.
     *
     * @param string $location
     *
     * @return $this
     */
    public function addLocation($location)
    {
        $this->locations[] = $location;
        return $this;
    }

    /**
     * Returns the locations to look in.
     *
     * @return array
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * Attempts to load the class if it exists.
     *
     * @param string $class
     *
     * @return bool
     */
    public function load($class)
    {
        $path = str_replace('\\', DS, $class);
        $path = '##replace##/' . trim($path, DS) . '.php';
        $path = str_replace('/', DS, $path);

        foreach ($this->getLocations() as $subPath) {
            $actualPath = str_replace('##replace##', $subPath, $path);
            if (file_exists($actualPath)) {
                require_once($actualPath);
                return true;
            }
        }
        return false;
    }
}
