<?php

namespace Parable\Framework;

class App
{
    const HOOK_HTTP_404             = "parable_http_404";
    const HOOK_HTTP_200             = "parable_http_200";
    const HOOK_RESPONSE_SEND        = "parable_response_send";
    const HOOK_LOAD_INITS_AFTER     = "parable_load_inits_after";
    const HOOK_ROUTE_MATCH_BEFORE   = "parable_route_match_before";
    const HOOK_ROUTE_MATCH_AFTER    = "parable_route_match_after";
    const HOOK_SESSION_START_BEFORE = "parable_session_start_before";
    const HOOK_SESSION_START_AFTER  = "parable_session_start_after";
    const HOOK_LOAD_ROUTES_BEFORE   = "parable_load_routes_before";
    const HOOK_LOAD_ROUTES_AFTER    = "parable_load_routes_after";
    const HOOK_INIT_DATABASE_BEFORE = "parable_init_database_before";
    const HOOK_INIT_DATABASE_AFTER  = "parable_init_database_after";


    /** @var \Parable\Filesystem\Path */
    protected $path;

    /** @var \Parable\Framework\Config */
    protected $config;

    /** @var \Parable\Framework\Dispatcher */
    protected $dispatcher;

    /** @var \Parable\Framework\Toolkit */
    protected $toolkit;

    /** @var \Parable\Event\Hook */
    protected $hook;

    /** @var \Parable\Routing\Router */
    protected $router;

    /** @var \Parable\Http\Response */
    protected $response;

    /** @var \Parable\Http\Url */
    protected $url;

    /** @var \Parable\GetSet\Session */
    protected $session;

    /** @var \Parable\ORM\Database */
    protected $database;

    /** @var string */
    protected $version = '0.12.13';

    public function __construct(
        \Parable\Filesystem\Path $path,
        \Parable\Framework\Config $config,
        \Parable\Framework\Dispatcher $dispatcher,
        \Parable\Framework\Toolkit $toolkit,
        \Parable\Event\Hook $hook,
        \Parable\Routing\Router $router,
        \Parable\Http\Response $response,
        \Parable\Http\Url $url,
        \Parable\GetSet\Session $session,
        \Parable\ORM\Database $database
    ) {
        $this->path       = $path;
        $this->config     = $config;
        $this->dispatcher = $dispatcher;
        $this->toolkit    = $toolkit;
        $this->hook       = $hook;
        $this->router     = $router;
        $this->response   = $response;
        $this->url        = $url;
        $this->session    = $session;
        $this->database   = $database;
    }

    /**
     * Do all the setup
     *
     * @return $this
     */
    public function run()
    {
        /* Load the config */
        $this->config->load();

        /* Set the basePath on the url based on the config */
        if ($this->config->get('parable.app.homeDir')) {
            $homeDir = trim($this->config->get('parable.app.homeDir'), "/");
            $this->url->setBasePath($homeDir);
        }

        /* See if there's any inits defined in the config */
        if ($this->config->get("parable.inits")) {
            $this->loadInits();
        }

        /* Start the session if session.autoEnable is true */
        if ($this->config->get('parable.session.autoEnable') !== false) {
            $this->startSession();
        }

        /* Build the base Url */
        $this->url->buildBaseurl();

        /* Load the routes */
        $this->loadRoutes();

        /* Get the current url */
        $currentUrl     = $this->toolkit->getCurrentUrl();
        $currentFullUrl = $this->toolkit->getCurrentUrlFull();

        /* Init the database if it's configured */
        if ($this->config->get('parable.database.type')) {
            $this->initDatabase();
        }

        /* And try to match the route */
        $this->hook->trigger(self::HOOK_ROUTE_MATCH_BEFORE, $currentUrl);
        $route = $this->router->matchUrl($currentUrl);
        $this->hook->trigger(self::HOOK_ROUTE_MATCH_AFTER, $route);

        if ($route) {
            $this->dispatchRoute($route);
        } else {
            $this->response->setHttpCode(404);
            $this->hook->trigger(self::HOOK_HTTP_404, $currentFullUrl);
        }

        $this->hook->trigger(self::HOOK_RESPONSE_SEND);
        $this->response->send();
        return $this;
    }

    /**
     * @return $this
     */
    protected function startSession()
    {
        $this->hook->trigger(self::HOOK_SESSION_START_BEFORE);
        $this->session->start();
        $this->hook->trigger(self::HOOK_SESSION_START_AFTER, $this->session);
        return $this;
    }

    /**
     * @return $this
     *
     * @throws \Parable\Framework\Exception
     */
    protected function loadRoutes()
    {
        $this->hook->trigger(self::HOOK_LOAD_ROUTES_BEFORE);
        if ($this->config->get("parable.routes")) {
            foreach ($this->config->get("parable.routes") as $routesClass) {
                /** @var \Parable\Framework\Interfaces\Routing $routes */
                $routes = \Parable\DI\Container::get($routesClass);

                if (!($routes instanceof \Parable\Framework\Interfaces\Routing)) {
                    throw new \Parable\Framework\Exception(
                        "{$routesClass} does not implement \Parable\Framework\Interfaces\Routing"
                    );
                }

                $this->router->addRoutes($routes->get());
            }
        }
        $this->hook->trigger(self::HOOK_LOAD_ROUTES_AFTER);
        return $this;
    }

    /**
     * Create instances of given init classes
     *
     * @return $this
     *
     * @throws \Parable\Framework\Exception
     */
    protected function loadInits()
    {
        if ($this->config->get("parable.inits")) {
            foreach ($this->config->get("parable.inits") as $initClass) {
                \Parable\DI\Container::create($initClass);
            }
        }
        $this->hook->trigger(self::HOOK_LOAD_INITS_AFTER);
        return $this;
    }

    /**
     * @return $this
     */
    protected function initDatabase()
    {
        $this->hook->trigger(self::HOOK_INIT_DATABASE_BEFORE);
        $this->database->setConfig($this->config->get('parable.database'));
        $this->hook->trigger(self::HOOK_INIT_DATABASE_AFTER);
        return $this;
    }

    /**
     * @param \Parable\Routing\Route $route
     *
     * @return $this
     */
    protected function dispatchRoute(\Parable\Routing\Route $route)
    {
        $this->response->setHttpCode(200);
        $this->hook->trigger(self::HOOK_HTTP_200, $route);

        $this->dispatcher->dispatch($route);

        return $this;
    }

    /**
     * Return the version number
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}
