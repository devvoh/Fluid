<?php

namespace Parable\Framework;

class App
{
    const PARABLE_VERSION                  = '1.1.0';

    const HOOK_HTTP_404                    = 'parable_http_404';
    const HOOK_HTTP_200                    = 'parable_http_200';
    const HOOK_LOAD_CONFIG_BEFORE          = 'parable_load_config_before';
    const HOOK_LOAD_CONFIG_AFTER           = 'parable_load_config_after';
    const HOOK_LOAD_INITS_BEFORE           = 'parable_load_inits_before';
    const HOOK_LOAD_INITS_AFTER            = 'parable_load_inits_after';
    const HOOK_INIT_DATABASE_BEFORE        = 'parable_init_database_before';
    const HOOK_INIT_DATABASE_AFTER         = 'parable_init_database_after';
    const HOOK_LOAD_LAYOUT_BEFORE          = 'parable_load_layout_before';
    const HOOK_LOAD_LAYOUT_AFTER           = 'parable_load_layout_after';
    const HOOK_LOAD_ROUTES_BEFORE          = 'parable_load_routes_before';
    const HOOK_LOAD_ROUTES_NO_ROUTES_FOUND = 'parable_load_routes_no_routes_found';
    const HOOK_LOAD_ROUTES_AFTER           = 'parable_load_routes_after';
    const HOOK_RESPONSE_SEND               = 'parable_response_send';
    const HOOK_ROUTE_MATCH_BEFORE          = 'parable_route_match_before';
    const HOOK_ROUTE_MATCH_AFTER           = 'parable_route_match_after';
    const HOOK_SESSION_START_BEFORE        = 'parable_session_start_before';
    const HOOK_SESSION_START_AFTER         = 'parable_session_start_after';

    /** @var \Parable\Framework\Config */
    protected $config;

    /** @var \Parable\Framework\Toolkit */
    protected $toolkit;

    /** @var \Parable\Framework\Package\PackageManager */
    protected $packageManager;

    /** @var \Parable\Framework\View */
    protected $view;

    /** @var \Parable\Event\Hook */
    protected $hook;

    /** @var \Parable\Filesystem\Path */
    protected $path;

    /** @var \Parable\Routing\Router */
    protected $router;

    /** @var \Parable\Http\Response */
    protected $response;

    /** @var \Parable\Http\Url */
    protected $url;

    /** @var bool */
    protected $errorReportingEnabled = false;

    public function __construct(
        \Parable\Framework\Autoloader $autoloader,
        \Parable\Framework\Config $config,
        \Parable\Framework\Toolkit $toolkit,
        \Parable\Framework\Package\PackageManager $packageManager,
        \Parable\Framework\View $view,
        \Parable\Event\Hook $hook,
        \Parable\Filesystem\Path $path,
        \Parable\Routing\Router $router,
        \Parable\Http\Response $response,
        \Parable\Http\Url $url
    ) {
        $this->config         = $config;
        $this->toolkit        = $toolkit;
        $this->packageManager = $packageManager;
        $this->view           = $view;
        $this->hook           = $hook;
        $this->path           = $path;
        $this->router         = $router;
        $this->response       = $response;
        $this->url            = $url;

        // Add the default location to the autoloader and register it
        $autoloader->addLocation(BASEDIR . DS . 'app');
        $autoloader->register();

        // And make sure $path has the proper BASEDIR
        $this->path = $path;
        $this->path->setBaseDir(BASEDIR);
    }

    /**
     * Do all the setup and then attempt to match and dispatch the current url.
     *
     * @return $this
     */
    public function run()
    {
        // And now possible packages get their turn.
        $this->packageManager->registerPackages();

        $this->loadConfig();

        // Init the database if it's configured
        if ($this->config->get('parable.database.type')) {
            $this->loadDatabase();
        }

        // Enable error reporting if debug is set to true
        if ($this->config->get('parable.debug') === true) {
            $this->setErrorReportingEnabled(true);
        } else {
            $this->setErrorReportingEnabled(false);
        }

        // Set the basePath on the url based on the config
        if ($this->config->get('parable.app.homedir')) {
            $homedir = trim($this->config->get('parable.app.homedir'), DS);
            $this->url->setBasePath($homedir);
        }

        // See if there's any inits defined in the config
        if ($this->config->get('parable.inits')) {
            $this->loadInits();
        }

        // Start the session if session.auto-enable is true
        if ($this->config->get('parable.session.auto-enable') !== false) {
            $this->startSession();
        }

        // Set the default timezone if it's set
        if ($this->config->get('parable.timezone')) {
            date_default_timezone_set($this->config->get('parable.timezone'));
        }

        // Build the base Url
        $this->url->buildBaseurl();

        // Load the routes
        $this->loadRoutes();

        // Get the current url
        $currentUrl     = $this->toolkit->getCurrentUrl();
        $currentFullUrl = $this->toolkit->getCurrentUrlFull();

        // And try to match the route
        $this->hook->trigger(self::HOOK_ROUTE_MATCH_BEFORE, $currentUrl);
        $route = $this->router->matchUrl($currentUrl);
        $this->hook->trigger(self::HOOK_ROUTE_MATCH_AFTER, $route);

        if ($route) {
            $this->dispatchRoute($route);
        } else {
            $this->response->setHttpCode(404);
            $this->hook->trigger(self::HOOK_HTTP_404, $currentFullUrl);
        }

        $this->loadLayout();

        $this->hook->trigger(self::HOOK_RESPONSE_SEND);
        $this->response->send();
        return $this;
    }

    /**
     * Return Parable's current version number.
     *
     * @return string
     */
    public function getVersion()
    {
        return self::PARABLE_VERSION;
    }

    /**
     * Enable error reporting, setting display_errors to on and reporting to E_ALL
     *
     * @param bool $enabled
     *
     * @return $this
     */
    public function setErrorReportingEnabled($enabled)
    {
        ini_set('log_errors', 1);

        if ($enabled) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 0);
            error_reporting(E_ALL | ~E_DEPRECATED);
        }

        $this->errorReportingEnabled = $enabled;

        return $this;
    }

    /**
     * Return whether error reporting is currently enabled or not
     *
     * @return bool
     */
    public function isErrorReportingEnabled()
    {
        return $this->errorReportingEnabled;
    }

    /**
     * Start the session.
     *
     * @return $this
     */
    protected function startSession()
    {
        $this->hook->trigger(self::HOOK_SESSION_START_BEFORE);

        $session = \Parable\DI\Container::get(\Parable\GetSet\Session::class);
        $session->start();

        $this->hook->trigger(self::HOOK_SESSION_START_AFTER, $session);
        return $this;
    }

    /**
     * Load all the routes, if possible.
     *
     * @return $this
     * @throws \Parable\Framework\Exception
     */
    protected function loadRoutes()
    {
        $this->hook->trigger(self::HOOK_LOAD_ROUTES_BEFORE);
        if ($this->config->get('parable.routes')) {
            foreach ($this->config->get('parable.routes') as $routesClass) {
                $routes = \Parable\DI\Container::create($routesClass);

                if (!($routes instanceof \Parable\Framework\Routing\AbstractRouting)) {
                    throw new \Parable\Framework\Exception(
                        "{$routesClass} does not extend \Parable\Framework\Routing\AbstractRouting"
                    );
                }

                // Load the routes
                $routes->load();
            }
        } else {
            $this->hook->trigger(self::HOOK_LOAD_ROUTES_NO_ROUTES_FOUND);
        }
        $this->hook->trigger(self::HOOK_LOAD_ROUTES_AFTER);
        return $this;
    }

    /**
     * Load the config and trigger hooks.
     */
    protected function loadConfig()
    {
        $this->hook->trigger(self::HOOK_LOAD_CONFIG_BEFORE);
        $this->config->load();
        $this->hook->trigger(self::HOOK_LOAD_CONFIG_AFTER);
    }

    /**
     * Create instances of given init classes.
     *
     * @return $this
     */
    protected function loadInits()
    {
        $this->hook->trigger(self::HOOK_LOAD_INITS_BEFORE);

        if ($this->config->get('parable.inits')) {
            $initLoader = \Parable\DI\Container::create(\Parable\Framework\Loader\InitLoader::class);
            $initLoader->load($this->config->get('parable.inits'));
        }

        $this->hook->trigger(self::HOOK_LOAD_INITS_AFTER);
        return $this;
    }

    /**
     * Initialize the database instance with data from the config.
     *
     * @return $this
     */
    protected function loadDatabase()
    {
        $this->hook->trigger(self::HOOK_INIT_DATABASE_BEFORE);

        $database = \Parable\DI\Container::get(\Parable\ORM\Database::class);
        $database->setConfig($this->config->get('parable.database'));

        $this->hook->trigger(self::HOOK_INIT_DATABASE_AFTER);
        return $this;
    }

    /**
     * Load the layout header/footer if configured.
     *
     * @return $this
     */
    protected function loadLayout()
    {
        $this->hook->trigger(self::HOOK_LOAD_LAYOUT_BEFORE);

        if ($this->config->get('parable.layout.header')) {
            $this->response->setHeaderContent(
                $this->view->partial($this->config->get('parable.layout.header'))
            );
        }
        if ($this->config->get('parable.layout.footer')) {
            $this->response->setFooterContent(
                $this->view->partial($this->config->get('parable.layout.footer'))
            );
        }

        $this->hook->trigger(self::HOOK_LOAD_LAYOUT_AFTER);
        return $this;
    }

    /**
     * Dispatch the provided route.
     *
     * @param \Parable\Routing\Route $route
     *
     * @return $this
     */
    protected function dispatchRoute(\Parable\Routing\Route $route)
    {
        $this->response->setHttpCode(200);
        $this->hook->trigger(self::HOOK_HTTP_200, $route);

        $dispatcher = \Parable\DI\Container::get(\Parable\Framework\Dispatcher::class);
        $dispatcher->dispatch($route);

        return $this;
    }

    /**
     * Add a route which will respond to all methods passed in $methods.
     *
     * The name is just a uniqid since quick routes aren't intended to be used the same as full routes.
     *
     * A valid callable is anything that is considered 'invokable' (function, a class with __invoke, an anonymous
     * function), but this also includes an array of a class and a method (["Controller", "indexAction"]). In these
     * cases, the action normally must be a static function. Parable solves this by checking whether the action is
     * static or not. If it isn't, it doesn't set the Callable as a callable, but it sets it as Controller and Action
     * separately to make sure things aren't loaded until they have to be.
     *
     * @param string[]    $methods
     * @param string      $url
     * @param callable    $callable
     * @param string|null $name
     * @param string|null $templatePath
     *
     * @return $this
     */
    public function multiple(array $methods, $url, $callable, $name = null, $templatePath = null)
    {
        $routeData = [
            'methods'      => $methods,
            'url'          => $url,
            'templatePath' => $templatePath,
        ];

        if (is_array($callable)
            && count($callable) === 2
            && class_exists($callable[0])
            && !(new \ReflectionMethod($callable[0], $callable[1]))->isStatic()
        ) {
            $routeData['controller'] = $callable[0];
            $routeData['action']     = $callable[1];
        } else {
            $routeData['callable']   = $callable;
        }

        $this->router->addRouteFromArray(
            $name ?: uniqid('', true),
            $routeData
        );

        return $this;
    }

    /**
     * @param string      $url
     * @param callable    $callable
     * @param string|null $name
     * @param string|null $templatePath
     *
     * @return $this
     */
    public function any($url, $callable, $name = null, $templatePath = null)
    {
        $this->multiple(\Parable\Http\Request::VALID_METHODS, $url, $callable, $name, $templatePath);
        return $this;
    }

    /**
     * Add a GET route with a callable and optionally a templatePath.
     *
     * @param string      $url
     * @param callable    $callable
     * @param string|null $name
     * @param string|null $templatePath
     *
     * @return $this
     */
    public function get($url, $callable, $name = null, $templatePath = null)
    {
        $this->multiple([\Parable\Http\Request::METHOD_GET], $url, $callable, $name, $templatePath);
        return $this;
    }

    /**
     * Add a POST route with a callable and optionally a templatePath.
     *
     * @param string      $url
     * @param callable    $callable
     * @param string|null $name
     * @param string|null $templatePath
     *
     * @return $this
     */
    public function post($url, $callable, $name = null, $templatePath = null)
    {
        $this->multiple([\Parable\Http\Request::METHOD_POST], $url, $callable, $name, $templatePath);
        return $this;
    }

    /**
     * Add a PUT route with a callable and optionally a templatePath.
     *
     * @param string      $url
     * @param callable    $callable
     * @param string|null $name
     * @param string|null $templatePath
     *
     * @return $this
     */
    public function put($url, $callable, $name = null, $templatePath = null)
    {
        $this->multiple([\Parable\Http\Request::METHOD_PUT], $url, $callable, $name, $templatePath);
        return $this;
    }

    /**
     * Add a PATCH route with a callable and optionally a templatePath.
     *
     * @param string      $url
     * @param callable    $callable
     * @param string|null $name
     * @param string|null $templatePath
     *
     * @return $this
     */
    public function patch($url, $callable, $name = null, $templatePath = null)
    {
        $this->multiple([\Parable\Http\Request::METHOD_PATCH], $url, $callable, $name, $templatePath);
        return $this;
    }

    /**
     * Add a DELETE route with a callable and optionally a templatePath.
     *
     * @param string      $url
     * @param callable    $callable
     * @param string|null $name
     * @param string|null $templatePath
     *
     * @return $this
     */
    public function delete($url, $callable, $name = null, $templatePath = null)
    {
        $this->multiple([\Parable\Http\Request::METHOD_DELETE], $url, $callable, $name, $templatePath);
        return $this;
    }

    /**
     * Add an OPTIONS route with a callable and optionally a templatePath.
     *
     * @param string      $url
     * @param callable    $callable
     * @param string|null $name
     * @param string|null $templatePath
     *
     * @return $this
     */
    public function options($url, $callable, $name = null, $templatePath = null)
    {
        $this->multiple([\Parable\Http\Request::METHOD_OPTIONS], $url, $callable, $name, $templatePath);
        return $this;
    }
}
