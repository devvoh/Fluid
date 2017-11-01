<?php

namespace Parable\Routing;

class Route
{
    /** @var array */
    public $methods = [];

    /** @var null|string */
    public $name;

    /** @var null|string */
    public $url;

    /** @var null|string */
    public $controller;

    /** @var null|string */
    public $action;

    /** @var null|callable */
    public $callable;

    /** @var null|string */
    public $template;

    /** @var array */
    public $parameters = [];

    /** @var array */
    public $values = [];

    /** @var array */
    public $cleanValues = [];

    /**
     * @param array $data
     *
     * @throws \Parable\Routing\Exception
     */
    public function setData(array $data)
    {
        $this->methods    = isset($data['methods'])    ? $data['methods']    : [];
        $this->url        = isset($data['url'])        ? $data['url']        : null;
        $this->controller = isset($data['controller']) ? $data['controller'] : null;
        $this->action     = isset($data['action'])     ? $data['action']     : null;
        $this->callable   = isset($data['callable'])   ? $data['callable']   : null;
        $this->template   = isset($data['template'])   ? $data['template']   : null;

        if (!$this->controller && !$this->action && !$this->callable) {
            throw new \Parable\Routing\Exception('Either a controller/action combination or callable is required.');
        }
        if (empty($this->methods) || !is_array($data['methods'])) {
            throw new \Parable\Routing\Exception('Methods are required and must be passed as an array.');
        }

        $this->parseUrlParameters();
    }

    /**
     * Parse the parameters in $this->url and store them as index => name, so we can look for values later.
     * on.
     *
     * @return $this
     */
    protected function parseUrlParameters()
    {
        $urlParts = explode('/', $this->url);
        $this->parameters = [];
        foreach ($urlParts as $index => $part) {
            if (mb_substr($part, 0, 1) === '{' && mb_substr($part, -1) === '}') {
                $this->parameters[$index] = mb_substr($part, 1, -1);
            }
        }
        return $this;
    }

    /**
     * Take $url and get all the values and store them in $this->values.
     *
     * @param string $url
     *
     * @return array
     */
    protected function extractParameterValues($url)
    {
        $urlParts = explode('/', $url);
        $this->values = [];
        foreach ($this->parameters as $index => $name) {
            $value = $urlParts[$index];

            $validValue = $this->checkAndApplyParameterValueType($name, $value);
            if ($validValue === false) {
                $this->values = [];
                break;
            }

            $this->values[$name] = $validValue;
        }
        $this->cleanValues();
        return $this->values;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed|bool
     */
    protected function checkAndApplyParameterValueType($name, $value)
    {
        // If there's no : in the name, then it's not typed.
        if (strpos($name, ':') === false) {
            return $value;
        }
        list(, $type) = explode(":", $name);

        if ($type === "int") {
            if (is_numeric($value) && (int)$value == $value) {
                return (int)$value;
            }
        } elseif ($type === "float") {
            if (is_numeric($value) && (float)$value == $value) {
                return (float)$value;
            }
        }

        return false;
    }

    protected function removeParameterValueTypeFromName($name)
    {
        // If there's no : in the name, then it's not typed.
        if (strpos($name, ':') === false) {
            return $name;
        }
        list($key) = explode(":", $name);

        // All good, so return just the key
        return $key;
    }

    /**
     * Take $url and replace the 'values' with the parameters they represent. This gives us a 'corrected' url,
     * which can be directly matched with the route's url.
     *
     * @param string $url
     *
     * @return string
     */
    protected function injectParameters($url)
    {
        $urlParts = explode('/', $url);

        foreach ($this->values as $key => $value) {
            $foundKey = array_search($value, $urlParts);
            if ($foundKey !== false) {
                $urlParts[$foundKey] = '{' . ltrim($key, '{}') . '}';
            }
        }
        return implode('/', $urlParts);
    }

    /**
     * Attempt to match $url to this route's url directly.
     *
     * @param string $url
     *
     * @return bool
     */
    public function matchDirectly($url)
    {
        if (!$this->isAcceptedRequestMethod()) {
            return false;
        }

        if (rtrim($this->url, "/") === rtrim($url, "/")) {
            return true;
        }
        return false;
    }

    /**
     * Attempt to match $url to this route's url but also extract parameters & values.
     *
     * @param string $url
     *
     * @return bool
     */
    public function matchWithParameters($url)
    {
        if (!$this->parameters
            || !$this->isAcceptedRequestMethod()
            || !$this->isPartCountSame($url)
            || !$this->hasParameters()
        ) {
            return false;
        }

        $this->extractParameterValues($url);
        if (!$this->values) {
            return false;
        }
        $correctedUrl = $this->injectParameters($url);

        if ($this->matchDirectly($correctedUrl)) {
            return true;
        }
        return false;
    }

    /**
     * Verify whether the request method is an accepted method according to $this->methods.
     *
     * @return bool
     */
    public function isAcceptedRequestMethod()
    {
        return in_array($_SERVER['REQUEST_METHOD'], $this->methods);
    }

    /**
     * Verify whether this route's url has the same 'part' count.
     *
     * @param string $url
     *
     * @return bool
     */
    public function isPartCountSame($url)
    {
        return count(explode('/', rtrim($url, '/'))) === count(explode('/', rtrim($this->url, '/')));
    }

    /**
     * Check whether the url has parameters
     *
     * @return bool
     */
    public function hasParameters()
    {
        return mb_strpos($this->url, '{') && mb_strpos($this->url, '}');
    }

    /**
     * @return $this
     */
    protected function cleanValues()
    {
        foreach ($this->values as $key => $value) {
            $key = $this->removeParameterValueTypeFromName($key);
            $this->cleanValues[$key] = $value;
        }
        return $this;
    }


    /**
     * Get a value, if it exists.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getValue($key)
    {
        if (!isset($this->cleanValues[$key])) {
            return null;
        }
        return $this->cleanValues[$key];
    }

    /**
     * Get all values
     *
     * @return array
     */
    public function getValues()
    {
        return $this->cleanValues;
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    public function buildUrlWithParameters(array $parameters = [])
    {
        $url = $this->url;

        if (!$this->hasParameters() && !$parameters) {
            return $url;
        }

        foreach ($parameters as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        return $url;
    }
}
