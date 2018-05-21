<?php

namespace Parable\GetSet;

abstract class Base
{
    /** @var null|string */
    protected $resource;

    /** @var bool*/
    protected $useLocalResource = false;

    /** @var array */
    protected $localResource = [];

    /**
     * Set the resource.
     *
     * @param string $resource
     *
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Return the resource.
     *
     * @return null|string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Return all from resource if resource is set.
     *
     * @return array
     * @throws \Parable\GetSet\Exception
     */
    public function getAll()
    {
        if (!$this->getResource()) {
            return [];
        }
        if ($this->useLocalResource) {
            return $this->localResource;
        }

        // If we're attempting to use a global resource but it doesn't exist, we've got a problem.
        if (!isset($GLOBALS[$this->getResource()])) {
            throw new \Parable\GetSet\Exception(
                "Attempting to use global resource '{$this->getResource()}' but resource not available."
            );
        }

        return $GLOBALS[$this->getResource()];
    }

    /**
     * Return specific value by key if resource set. If not found, return default.
     *
     * $getSet->get("one.two.three", "value") would return $resource["one"]["two"]["three"] or "value";
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        $resource = $this->getAll();

        $keys = explode('.', $key);
        foreach ($keys as $key) {
            if (!isset($resource[$key])) {
                $resource = $default;
                break;
            }
            $resource = &$resource[$key];
        }

        return $resource;
    }

    /**
     * Return all from resource and then clear it.
     *
     * @return array
     */
    public function getAllAndReset()
    {
        $data = $this->getAll();
        $this->reset();
        return $data;
    }

    /**
     * Return specific value by key and then clear it.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getAndRemove($key)
    {
        $data = $this->get($key);
        if ($data) {
            $this->remove($key);
        }
        return $data;
    }

    /**
     * Return the number of items on the first level of the resource.
     *
     * @return int
     */
    public function count()
    {
        return count($this->getAll());
    }

    /**
     * Set specific value by key if resource set. It's possible to set using . to separate keys by depth.
     *
     * $getSet->set("one.two.three", "value") is equal to $resource["one"]["two"]["three"] = $value;
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $keys = explode('.', $key);

        $data = $this->getAll();

        $resource = &$data;
        foreach ($keys as $key) {
            if (!isset($resource[$key]) || !is_array($resource[$key])) {
                $resource[$key] = [];
            }
            $resource = &$resource[$key];
        }
        $resource = $value;

        $this->setAll($data);

        return $this;
    }

    /**
     * Set all key/value pairs in $values if resource is set and $values is an array
     *
     * @param array $values
     *
     * @return $this
     */
    public function setMany(array $values)
    {
        if ($this->getResource()) {
            foreach ($values as $key => $value) {
                $this->set($key, $value);
            }
        }
        return $this;
    }

    /**
     * Set entire array onto the resource
     *
     * @param array $values
     *
     * @return $this
     */
    public function setAll(array $values)
    {
        if ($this->getResource()) {
            if ($this->useLocalResource) {
                $this->localResource = $values;
            } else {
                $GLOBALS[$this->getResource()] = $values;
            }
        }
        return $this;
    }

    /**
     * Remove an item by key. It's possible to use the dot-notation (->remove("one.two")).
     *
     * @param string $key
     *
     * @return $this
     */
    public function remove($key)
    {
        $keys = explode('.', $key);

        $data = $this->getAll();

        $resource = &$data;
        foreach ($keys as $index => $key) {
            if (!isset($resource[$key])) {
                // We bail, the requested key could not be found
                return $this;
            }
            if ($index < (count($keys) - 1)) {
                $resource = &$resource[$key];
            }
        }
        unset($resource[$key]);

        $this->setAll($data);

        return $this;
    }

    /**
     * Reset the resource to contain nothing at all.
     *
     * @return $this
     */
    public function reset()
    {
        $this->setAll([]);
        return $this;
    }
}
