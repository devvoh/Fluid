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
     * Returns the resource type
     *
     * @return null|string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get all from resource if resource set
     *
     * @return array
     */
    public function getAll()
    {
        if (!$this->getResource()) {
            return [];
        }
        if ($this->useLocalResource) {
            return $this->localResource;
        }
        return $GLOBALS[$this->getResource()];
    }

    /**
     * Get specific value by key if resource set
     *
     * $getSet->get("one.two.three", "value") would return $resource["one"]["two"]["three"];
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        $resource = $this->getAll();

        $keys = explode(".", $key);
        foreach ($keys as $key) {
            if (!isset($resource[$key])) {
                $resource = null;
                break;
            }
            $resource = &$resource[$key];
        }

        return $resource;
    }

    /**
     * @return array
     */
    public function getAllAndReset()
    {
        $data = $this->getAll();
        $this->reset();
        return $data;
    }

    /**
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
        $keys = explode(".", $key);

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
     * @param string $key
     *
     * @return $this
     */
    public function remove($key)
    {
        $keys = explode(".", $key);

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
     * @return $this
     */
    public function reset()
    {
        $this->setAll([]);
        return $this;
    }
}
