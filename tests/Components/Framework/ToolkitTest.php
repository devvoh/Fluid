<?php

namespace Parable\Tests\Components\Framework;

class ToolkitTest extends \Parable\Tests\Components\Framework\Base
{
    /** @var \Parable\Framework\Toolkit */
    protected $toolkit;

    /** @var \Parable\Http\Response|\PHPUnit_Framework_MockObject_MockObject */
    protected $responseMock;

    /** @var \Parable\Routing\Router */
    protected $router;

    protected function setUp()
    {
        parent::setUp();

        $this->responseMock = $this->createPartialMock(\Parable\Http\Response::class, ['terminate']);
        $this->responseMock->__construct(\Parable\DI\Container::get(\Parable\Http\Request::class));
        \Parable\DI\Container::store($this->responseMock, \Parable\Http\Response::class);

        $this->router = \Parable\DI\Container::get(\Parable\Routing\Router::class);
        $this->router->addRouteFromArray('simple', [
            'methods' => ['GET'],
            'url' => '/',
            'controller' => \Parable\Tests\TestClasses\Controller::class,
            'action' => 'simple',
        ]);
        $GLOBALS['_GET'] = [
            'url' => 'this/was/requested',
        ];
        $this->toolkit = \Parable\DI\Container::create(\Parable\Framework\Toolkit::class);
    }

    public function testRedirectToRoute()
    {
        // The only way to test this is to see if terminate is called
        $this->responseMock->expects($this->once())->method('terminate');
        $this->toolkit->redirectToRoute("simple");
    }

    public function testRedirectToRouteThrowsExceptionOnNonExistingRouteName()
    {
        $this->expectException(\Parable\Framework\Exception::class);
        $this->expectExceptionMessage("Can't redirect to route, 'this-aint-no-route' does not exist.");

        $this->responseMock->expects($this->never())->method('terminate');
        $this->toolkit->redirectToRoute("this-aint-no-route");
    }

    public function testGetFullRouteUrlByName()
    {
        $routeUrl = $this->toolkit->getFullRouteUrlByName("simple");

        $this->assertSame("http://www.test.dev/test/", $routeUrl);
    }

    public function testGetFullRouteUrlByNameReturnsNullOnNonExistingRouteName()
    {
        $routeUrl = $this->toolkit->getFullRouteUrlByName("does-not-exist");

        $this->assertNull($routeUrl);
    }

    public function testAppQuickRouteAndGetFullRouteUrlByName()
    {
        $app = \Parable\DI\Container::create(\Parable\Framework\App::class);
        $app->get("/test-route/{id}", function () {
            return "yeah";
        }, "test-route");

        $url = $this->toolkit->getFullRouteUrlByName("test-route", ["id" => 1337]);

        $this->assertSame("http://www.test.dev/test/test-route/1337", $url);
    }

    public function testGetCurrentUrl()
    {
        $this->assertSame('this/was/requested', $this->toolkit->getCurrentUrl());
    }

    public function testGetCurrentUrlReturnsEmptyUrlIfNoUrlKnown()
    {
        unset($GLOBALS['_GET']['url']);
        $this->assertSame('/', $this->toolkit->getCurrentUrl());
    }

    public function testGetCurrentUrlFull()
    {
        $this->assertSame('http://www.test.dev/test/this/was/requested', $this->toolkit->getCurrentUrlFull());
    }
}
