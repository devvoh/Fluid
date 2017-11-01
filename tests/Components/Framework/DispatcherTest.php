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

    protected function setUp()
    {
        parent::setUp();

        $this->dispatcher = \Parable\DI\Container::create(\Parable\Framework\Dispatcher::class);
        $this->hook       = \Parable\DI\Container::get(\Parable\Event\Hook::class);
        $this->internal   = \Parable\DI\Container::get(\Parable\GetSet\Internal::class);

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
        $route->setData([
            'methods' => ['GET'],
            'url' => '/',
            'controller' => \Parable\Tests\TestClasses\Controller::class,
            'action' => 'index',
        ]);

        $this->assertSame($this->dispatcher, $this->dispatcher->dispatch($route));

        $this->assertSame($route, $this->internal->get('parable_dispatch_after'));
    }
}
