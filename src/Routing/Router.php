<?php

namespace Parable\Routing;

class Router
{
    /** @var \Parable\Routing\Route[] */
    protected $routes = [];

    /**
     * Add a route to the routes list.
     *
     * @param string $name
     * @param array $routeArray
     *
     * @return $this
     */
    public function addRoute($name, array $routeArray)
    {
        $route = new \Parable\Routing\Route();
        $route->setData($routeArray);
        $this->routes[$name] = $route;
        return $this;
    }

    /**
     * @param array $routes
     *
     * @return $this
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $name => $route) {
            $this->addRoute($name, $route);
        }
        return $this;
    }

    /**
     * Return a route by its name.
     *
     * @param string $name
     *
     * @return \Parable\Routing\Route|null
     */
    public function getRouteByName($name)
    {
        if (!isset($this->routes[$name])) {
            return null;
        }
        return $this->routes[$name];
    }

    /**
     * Try to find a match in all available routes.
     *
     * @param string $url
     *
     * @return \Parable\Routing\Route|null
     */
    public function matchUrl($url)
    {
        $url = '/' . ltrim($url, '/');
        if ($route = $this->matchUrlDirectly($url)) {
            return $route;
        }
        if ($route = $this->matchUrlWithParameters($url)) {
            return $route;
        }
        return null;
    }

    /**
     * Loop through routes and try to match directly.
     *
     * @param string $url
     *
     * @return \Parable\Routing\Route|null
     */
    protected function matchUrlDirectly($url)
    {
        foreach ($this->routes as $route) {
            if ($route->matchDirectly($url)) {
                return $route;
            }
        }
        return null;
    }

    /**
     * Loop through routes and try to match with parameters.
     *
     * @param string $url
     *
     * @return \Parable\Routing\Route|null
     */
    protected function matchUrlWithParameters($url)
    {
        foreach ($this->routes as $route) {
            if ($route->matchWithParameters($url)) {
                return $route;
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    public function getRouteUrlByName($name, array $parameters = [])
    {
        $route = $this->getRouteByName($name);
        if (!$route) {
            return null;
        }
        return $route->buildUrlWithParameters($parameters);
    }
}
