<?php

namespace Parable\Framework;

class Toolkit
{
    /** @var \Parable\GetSet\Get */
    protected $get;

    /** @var \Parable\Http\Response */
    protected $response;

    /** @var \Parable\Http\Url */
    protected $url;

    /** @var \Parable\Routing\Router */
    protected $router;

    public function __construct(
        \Parable\GetSet\Get $get,
        \Parable\Http\Response $response,
        \Parable\Http\Url $url,
        \Parable\Routing\Router $router
    ) {
        $this->get      = $get;
        $this->response = $response;
        $this->url      = $url;
        $this->router   = $router;
    }

    /**
     * Create a repository to work with model of type $modelName (full namespaced name).
     *
     * @param string $modelName
     *
     * @return \Parable\ORM\Repository
     */
    public function getRepository($modelName)
    {
        return \Parable\ORM\Repository::createForModelName($modelName);
    }

    /**
     * Redirect directly by using a route name.
     *
     * @param string $routeName
     * @param array  $parameters
     *
     * @throws \Parable\Framework\Exception
     */
    public function redirectToRoute($routeName, array $parameters = [])
    {
        $url = $this->router->getRouteUrlByName($routeName, $parameters);
        if (!$url) {
            throw new \Parable\Framework\Exception("Can't redirect to route, '{$routeName}' does not exist.");
        }
        $this->response->redirect($this->url->getUrl($url));
    }

    /**
     * Return full url from a route by $name, passing $parameters on (as [key => value]).
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    public function getFullRouteUrlByName($name, array $parameters = [])
    {
        $routeUrl = $this->router->getRouteUrlByName($name, $parameters);
        if ($routeUrl === null) {
            return null;
        }
        return $this->url->getUrl($routeUrl);
    }

    /**
     * Return the current url as interpreted by Parable.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        if ($this->get->get('url')) {
            return $this->get->get('url');
        }
        return '/';
    }

    /**
     * Return the current url as interpreted by Parable, as a full url.
     *
     * @return string
     */
    public function getCurrentUrlFull()
    {
        return $this->url->getUrl($this->getCurrentUrl());
    }
}
