<?php

namespace Parable\Routing;

class Route
{
    /** @var array */
    protected $methods = [];

    /** @var null|string */
    protected $name;

    /** @var null|string */
    protected $url;

    /** @var null|string */
    protected $controller;

    /** @var null|string */
    protected $action;

    /** @var null|callable */
    protected $callable;

    /** @var null|string */
    protected $templatePath;

    /** @var array */
    protected $parameters = [];

    /** @var array */
    protected $values = [];

    /**
     * Set data from array. Can only set values that have a corresponding setProperty method.
     *
     * @param array $data
     *
     * @return $this
     * @throws \Parable\Routing\Exception
     */
    public function setDataFromArray(array $data)
    {
        foreach ($data as $property => $value) {
            $method = 'set' . ucfirst($property);
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            } else {
                throw new \Parable\Routing\Exception(
                    "Tried to set non-existing property '{$property}' with value '{$value}' on " . get_class($this)
                );
            }
        }

        $this->checkValidProperties();
        $this->parseUrlParameters();

        return $this;
    }

    /**
     * Set the methods accepted by this Route (POST, GET, PUT, etc.) and make sure they're uppercase.
     *
     * @param string[] $methods
     *
     * @return $this
     */
    public function setMethods(array $methods)
    {
        foreach ($methods as &$method) {
            $method = strtoupper($method);
        }
        $this->methods = $methods;
        return $this;
    }

    /**
     * Return the methods accepted by this Route
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Set the url this route is matched on.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        if (strpos($url, '/') !== 0) {
            $url = '/' . $url;
        }
        $this->url = $url;
        return $this;
    }

    /**
     * Return the url this route is matched on.
     *
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the controller name for this Route.
     *
     * @param string $controller
     *
     * @return $this
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Return the controller name for this Route.
     *
     * @return null|string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Set the action for this Route.
     *
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Return the action for this Route.
     *
     * @return null|string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the name for this Route.
     *
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Return the name for this Route.
     *
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the callable for this Route.
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function setCallable(callable $callable)
    {
        $this->callable = $callable;
        return $this;
    }

    /**
     * Return the callable for this Route.
     *
     * @return callable|null
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * Set the template path for this Route.
     *
     * @param string $templatePath
     *
     * @return $this
     */
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;
        return $this;
    }

    /**
     * Return the template path for this Route.
     *
     * @return mixed
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     * Check whether a valid set of properties is set.
     *
     * @return $this
     * @throws \Parable\Routing\Exception
     */
    public function checkValidProperties()
    {
        if (!$this->controller && !$this->action && !$this->callable) {
            throw new \Parable\Routing\Exception('Either a controller/action combination or callable is required.');
        }
        if (empty($this->methods)) {
            throw new \Parable\Routing\Exception('Methods are required and must be passed as an array.');
        }
        return $this;
    }

    /**
     * Return whether the Route has a controller AND an action set.
     *
     * @return bool
     */
    public function hasControllerAndAction()
    {
        return (bool)$this->getController() && (bool)$this->getAction();
    }

    /**
     * Return whether the Route has a callable set.
     *
     * @return bool
     */
    public function hasCallable()
    {
        return (bool)$this->getCallable();
    }

    /**
     * Return whether the Route has a template path set.
     *
     * @return bool
     */
    public function hasTemplatePath()
    {
        return (bool)$this->getTemplatePath();
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
            $value = trim($urlParts[$index]);
            if (!empty($value)) {
                $this->setValue($name, $value);
            }
        }
        return $this->values;
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
        if (!$this->isAcceptedRequestMethod()
            || !$this->isPartCountSame($url)
            || !$this->hasParameters()
        ) {
            return false;
        }

        $this->extractParameterValues($url);
        $correctedUrl = $this->injectParameters($url);

        return $this->matchDirectly($correctedUrl);
    }

    /**
     * Verify whether the request method is an accepted method according to $this->methods.
     *
     * @return bool
     */
    public function isAcceptedRequestMethod()
    {
        return in_array($_SERVER['REQUEST_METHOD'], $this->getMethods());
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
     * Set a value on the route.
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function setValue($key, $value)
    {
        $this->values[$key] = $value;
        return $this;
    }

    /**
     * @param array $values
     */
    public function setValues(array $values)
    {
        foreach ($values as $key => $value) {
            $this->setValue($key, $value);
        }
    }

    /**
     * Return a value, if it exists.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getValue($key)
    {
        if (!isset($this->values[$key])) {
            return null;
        }
        return $this->values[$key];
    }

    /**
     * Return all values.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Build a url based on the route, replacing all parameters with the values passed (as [key => value]).
     *
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

    /**
     * Create new route based on data from array.
     *
     * @param array $data
     *
     * @return static
     */
    public static function createFromDataArray(array $data)
    {
        $route = new static();
        $route->setDataFromArray($data);
        return $route;
    }
}
