<?php

namespace Parable\Tests\Components\Framework;

class DispatcherTest extends \Parable\Tests\Components\Framework\Base
{
    /** @var \Parable\Framework\Dispatcher */
    protected $dispatcher;

    /** @var \Parable\Event\Hook */
    protected $hook;

    /** @var \Parable\GetSet\Internal */
    protected $internal;

    /** @var \Parable\Http\Response */
    protected $response;

    protected function setUp()
    {
        parent::setUp();

        $this->dispatcher = \Parable\DI\Container::create(\Parable\Framework\Dispatcher::class);
        $this->hook       = \Parable\DI\Container::get(\Parable\Event\Hook::class);
        $this->internal   = \Parable\DI\Container::get(\Parable\GetSet\Internal::class);
        $this->response   = \Parable\DI\Container::get(\Parable\Http\Response::class);

        $internal = $this->internal;

        $callback = function ($event, $payload) use ($internal) {
            $this->internal->set($event, $payload);
        };

        $this->hook->into('parable_dispatch_before', $callback);
        $this->hook->into('parable_dispatch_after', $callback);
    }

    public function testDispatchRoute()
    {
        $route = new \Parable\Routing\Route();
        $route->setDataFromArray([
            'methods' => ['GET'],
            'url' => '/',
            'controller' => \Parable\Tests\TestClasses\Controller::class,
            'action' => 'index',
        ]);

        $this->assertSame($this->dispatcher, $this->dispatcher->dispatch($route));

        $this->assertSame($route, $this->internal->get('parable_dispatch_after'));
    }

    public function testMagicTemplatePathFromControllerAndActionWorks()
    {
        $route = new \Parable\Routing\Route();
        $route->setMethods(["get"]);
        $route->setUrl("/");
        $route->setController(\Parable\Tests\TestClasses\Controller::class);
        $route->setAction("index");

        $path = new \Parable\Filesystem\Path();
        $path->setBaseDir($this->testPath->getBaseDir() . DS . "tests/TestTemplates/");

        $dispatcher = new \Parable\Framework\Dispatcher(
            $this->hook,
            $path,
            \Parable\DI\Container::get(\Parable\Framework\View::class),
            $this->response
        );

        $dispatcher->dispatch($route);

        $this->assertSame($route, $this->internal->get('parable_dispatch_after'));

        $this->assertContains(
            "app/View/Parable/Tests/TestClasses/Controller/index.phtml",
            $route->getTemplatePath()
        );
        $this->assertSame("stuff from the template", $this->response->getContent());
    }

    public function testGetRouteReturnsNullIfNothingDispatched()
    {
        $this->assertNull($this->dispatcher->getDispatchedRoute());
    }

    public function testGetRouteReturnsRouteIfRouteDispatched()
    {
        $route = new \Parable\Routing\Route();
        $route->setDataFromArray([
            'methods' => ['GET'],
            'url' => '/',
            'controller' => \Parable\Tests\TestClasses\Controller::class,
            'action' => 'index',
        ]);

        $this->dispatcher->dispatch($route);

        $this->assertSame($route, $this->dispatcher->getDispatchedRoute());
    }
}
