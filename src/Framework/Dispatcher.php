<?php

namespace Parable\Framework;

class Dispatcher
{
    const HOOK_DISPATCH_BEFORE          = 'parable_dispatch_before';
    const HOOK_DISPATCH_AFTER           = 'parable_dispatch_after';
    const HOOK_DISPATCH_TEMPLATE_BEFORE = 'parable_dispatch_template_before';
    const HOOK_DISPATCH_TEMPLATE_AFTER  = 'parable_dispatch_template_after';

    /** @var \Parable\Event\Hook */
    protected $hook;

    /** @var \Parable\Filesystem\Path */
    protected $path;

    /** @var \Parable\Framework\View */
    protected $view;

    /** @var \Parable\Http\Response */
    protected $response;

    /** @var \Parable\Routing\Route|null */
    protected $dispatchedRoute;

    public function __construct(
        \Parable\Event\Hook $hook,
        \Parable\Filesystem\Path $path,
        \Parable\Framework\View $view,
        \Parable\Http\Response $response
    ) {
        $this->hook     = $hook;
        $this->path     = $path;
        $this->view     = $view;
        $this->response = $response;
    }

    /**
     * Dispatch the provided route.
     *
     * @param \Parable\Routing\Route $route
     *
     * @return $this
     */
    public function dispatch(\Parable\Routing\Route $route)
    {
        $this->dispatchedRoute = $route;

        $this->hook->trigger(self::HOOK_DISPATCH_BEFORE, $route);
        $controller = null;

        // Start output buffering and set $returnContent to null
        $returnContent = null;
        $this->response->startOutputBuffer();

        // Build the parameters array
        $parameters = [];
        foreach ($route->getValues() as $value) {
            $parameters[] = $value;
        }

        // Call the relevant code
        if ($route->hasControllerAndAction()) {
            $controller = \Parable\DI\Container::get($route->getController());
            $returnContent = $controller->{$route->getAction()}(...$parameters);
        } elseif ($route->hasCallable()) {
            $callable = $route->getCallable();
            $returnContent = $callable(...$parameters);
        }

        $this->hook->trigger(self::HOOK_DISPATCH_TEMPLATE_BEFORE, $route);

        // If the route has no template path, attempt to build one based on controller/action.phtml
        if (!$route->hasTemplatePath() && $route->hasControllerAndAction()) {
            $reflection = new \ReflectionClass($controller);
            $controllerName = str_replace('\\', '/', $reflection->getName());
            $controllerName = str_replace('Controller/', '', $controllerName);

            $templatePathGenerated = $this->path->getDir(
                "app/View/{$controllerName}/{$route->getAction()}.phtml"
            );

            if (file_exists($templatePathGenerated)) {
                $route->setTemplatePath($templatePathGenerated);
            }
        }

        // And check again, now that we might have a magic template path
        if ($route->hasTemplatePath()) {
            $templatePath = $this->path->getDir($route->getTemplatePath());
            $this->view->setTemplatePath($templatePath);
            $this->view->render();
        }

        // If the callback or controller/action returned content, we need to handle it
        if ($returnContent) {
            if ($this->response->getOutput()->acceptsContent($returnContent)) {
                $this->response->setContent($returnContent);
            } else {
                $type   = gettype($returnContent);
                $output = get_class($this->response->getOutput());

                // Stop the output buffer we've started above and throw
                $this->response->stopOutputBuffer();
                throw new \Parable\Framework\Exception(
                    "Route returned value of type '{$type}', which output class '{$output}' cannot handle."
                );
            }
        }

        // Any rendered content was from before the returnContent was set, so we prepend it if there's any
        $renderedContent = $this->response->returnOutputBuffer();
        if ($renderedContent) {
            $this->response->prependContent($renderedContent);
        }

        $this->hook->trigger(self::HOOK_DISPATCH_TEMPLATE_AFTER, $route);
        $this->hook->trigger(self::HOOK_DISPATCH_AFTER, $route);
        return $this;
    }

    /**
     * Return the route we've dispatched, if any.
     *
     * @return \Parable\Routing\Route|null
     */
    public function getDispatchedRoute()
    {
        return $this->dispatchedRoute;
    }
}
