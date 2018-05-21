<?php

namespace Parable\DI;

class Container
{
    /** @var array */
    protected static $instances = [];

    /** @var array */
    protected static $relations = [];

    /**
     * Return an already instantiated instance or create a new one.
     *
     * @param string $className
     * @param string $parentClassName
     *
     * @return mixed
     * @throws \Parable\DI\Exception
     */
    public static function get($className, $parentClassName = '')
    {
        $className = self::cleanName($className);

        // We store the relationship between class & parent to prevent cyclical references
        if ($parentClassName) {
            self::$relations[$className][$parentClassName] = true;
        }

        // And we check for cyclical references to prevent infinite loops
        if ($parentClassName
            && isset(self::$relations[$parentClassName])
            && isset(self::$relations[$parentClassName][$className])
        ) {
            $message  = "Cyclical dependency found: {$className} depends on {$parentClassName}";
            $message .= " but is itself a dependency of {$parentClassName}.";
            throw new \Parable\DI\Exception($message);
        }

        if (!self::isStored($className)) {
            self::store(self::create($className, $parentClassName));
        }

        return self::$instances[$className];
    }

    /**
     * Instantiate a class and fulfill its dependency requirements, getting dependencies rather than creating.
     * This does not store the created instance in the cache. It would have to be manually stored.
     *
     * @param string $className
     * @param string $parentClassName
     *
     * @return mixed
     * @throws \Parable\DI\Exception
     */
    public static function create($className, $parentClassName = '')
    {
        return static::createInstance($className, $parentClassName, false);
    }

    /**
     * Instantiate a class and fulfill its dependency requirements, making sure ALL dependencies are created as well.
     *
     * @param string $className
     * @param string $parentClassName
     *
     * @return mixed
     * @throws \Parable\DI\Exception
     */
    public static function createAll($className, $parentClassName = '')
    {
        return static::createInstance($className, $parentClassName, true);
    }

    /**
     * Instantiate a class and fulfill its dependency requirements.
     *
     * @param string $className
     * @param string $parentClassName
     * @param bool   $createAll
     *
     * @return mixed
     * @throws \Parable\DI\Exception
     */
    protected static function createInstance($className, $parentClassName = '', $createAll = false)
    {
        $className = self::cleanName($className);

        try {
            $dependencies = self::getDependenciesFor($className, $createAll);
        } catch (\Parable\DI\Exception $e) {
            $message = $e->getMessage();
            if ($parentClassName) {
                $message .= ", required by '{$parentClassName}'";
            }
            throw new \Parable\DI\Exception($message);
        }
        return new $className(...$dependencies);
    }

    /**
     * Retrieve and instantiate all dependencies for the provided $className
     *
     * @param string $className
     * @param bool   $createAll
     *
     * @return array
     * @throws \Parable\DI\Exception
     */
    public static function getDependenciesFor($className, $createAll = false)
    {
        try {
            $reflection = new \ReflectionClass($className);
        } catch (\Exception $e) {
            $message = "Could not create instance of '{$className}'";
            throw new \Parable\DI\Exception($message);
        }

        $construct = $reflection->getConstructor();

        if (!$construct) {
            return [];
        }

        $parameters = $construct->getParameters();

        $dependencies = [];
        foreach ($parameters as $parameter) {
            $subClassName = $parameter->name;

            try {
                $class = $parameter->getClass();
                if (is_object($class)) {
                    $subClassName = $class->name;
                }
            } catch (\ReflectionException $e) {
            }

            if ($createAll) {
                $dependencies[] = self::createAll($subClassName, $className);
            } else {
                $dependencies[] = self::get($subClassName, $className);
            }
        }
        return $dependencies;
    }

    /**
     * Store an instance under either the provided $name or its class name.
     *
     * @param object      $instance
     * @param string|null $name
     */
    public static function store($instance, $name = null)
    {
        if (!$name) {
            $name = get_class($instance);
        }
        $name = self::cleanName($name);
        self::$instances[$name] = $instance;
    }

    /**
     * Check whether an instance with $name is currently stored.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function isStored($name)
    {
        return isset(self::$instances[$name]);
    }

    /**
     * Clean the provided name from any prefixed backslashes.
     *
     * @param string $name
     *
     * @return string
     */
    protected static function cleanName($name)
    {
        if (substr($name, 0, 1) === '\\') {
            $name = ltrim($name, '\\');
        }
        return $name;
    }

    /**
     * If the $name exists, clear it from our list of stored instances.
     *
     * @param string $name
     */
    public static function clear($name)
    {
        if (self::isStored($name)) {
            unset(self::$instances[$name]);
        }
    }

    /**
     * Remove all stored instances but KEEP the passed instance names.
     *
     * @param string[] $keepInstanceNames
     */
    public static function clearExcept(array $keepInstanceNames)
    {
        foreach (self::$instances as $name => $instance) {
            if (!in_array($name, $keepInstanceNames)) {
                self::clear($name);
            }
        }
    }

    /**
     * Remove all stored instances.
     */
    public static function clearAll()
    {
        self::clearExcept([]);
    }
}
