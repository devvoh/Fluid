<?php

namespace Parable\Tests\Components\Routing;

class RouterTest extends \Parable\Tests\Base
{
    /** @var \Parable\Routing\Router */
    protected $router;

    protected function setUp()
    {
        parent::setUp();

        // Since the Router depends on \Parable\Http\Request, which depends on some $_SERVER values, we set them
        $GLOBALS['_SERVER']['REQUEST_METHOD'] = "GET";

        $this->router = \Parable\DI\Container::create(\Parable\Routing\Router::class);

        $this->router->addRoutesFromArray([
            'simple' => [
                'methods' => ['GET'],
                'url' => '/',
                'controller' => \Parable\Tests\TestClasses\Controller::class,
                'action' => 'simple',
            ],
            'complex' => [
                'methods' => ['GET'],
                'url' => '/complex/{id}/{name}',
                'controller' => \Parable\Tests\TestClasses\Controller::class,
                'action' => 'complex',
            ],
            'callable' => [
                'methods' => ['GET'],
                'url' => '/callable/{parameter}',
                'callable' => function (\Parable\Routing\Route $route, $parameter) {
                    return [$route, $parameter];
                },
                'templatePath' => 'test-file.phtml',
            ]
        ]);
    }

    public function testInvalidRouteThrowsException()
    {
        $this->expectExceptionMessage("Either a controller/action combination or callable is required.");
        $this->expectException(\Parable\Routing\Exception::class);

        $this->router->addRouteFromArray('invalid', []);
    }

    public function testAddRouteNoMethodsThrowsException()
    {
        $this->expectExceptionMessage("Methods are required and must be passed as an array.");
        $this->expectException(\Parable\Routing\Exception::class);

        $this->router->addRouteFromArray('invalid', ['callable' => function () {
        }]);
    }

    public function testAddRouteInvalidMethodsAcceptedReturnsNull()
    {
        $_GET['url'] = '/easy';
        $this->router->addRouteFromArray('callable', [
            'methods' => ['GET'],
            'url' => '/easy',
            'callable' => function () {
            },
        ]);
        $this->assertInstanceOf(\Parable\Routing\Route::class, $this->router->matchUrl('/easy'));

        // Now re-add as a POST-only route
        $this->router->addRouteFromArray('callable', [
            'methods' => ['POST'],
            'url' => '/easy',
            'callable' => function () {
            },
        ]);
        $this->assertNull($this->router->matchUrl('/easy'));
    }

    public function testRouteAddedAndGetRouteByName()
    {
        $route = $this->router->getRouteByName('simple');

        $this->assertSame(['GET'], $route->getMethods());
        $this->assertSame('/', $route->getUrl());
        $this->assertSame(\Parable\Tests\TestClasses\Controller::class, $route->getController());
        $this->assertSame('simple', $route->getAction());

        $this->assertNull($route->getCallable());
        $this->assertNull($route->getTemplatePath());
    }

    public function testInvalidGetRouteByNameReturnsNull()
    {
        $this->assertNull($this->router->getRouteByName('la-dee-dah'));
    }

    public function testMatchUrlSimple()
    {
        $route = $this->router->matchUrl('/');

        $this->assertNotNull($route);

        $this->assertSame(['GET'], $route->getMethods());
        $this->assertSame('/', $route->getUrl());
        $this->assertSame(\Parable\Tests\TestClasses\Controller::class, $route->getController());
        $this->assertSame('simple', $route->getAction());

        $this->assertNull($route->getCallable());
        $this->assertNull($route->getTemplatePath());

        $this->assertFalse($route->hasParameters());
    }

    public function testMatchUrlSanitizesUrlAndStillMatches()
    {
        $_GET['url'] = '/<b>this-should-work</b>';
        $this->router->addRouteFromArray('callable', [
            'methods' => ['GET'],
            'url' => '/this-should-work',
            'callable' => function () {
                return "it did!";
            },
        ]);

        $route = $this->router->matchUrl('/this-should-work');

        $this->assertInstanceOf(\Parable\Routing\Route::class, $route);

        $callable = $route->getCallable();

        $this->assertSame("it did!", $callable());
    }

    public function testmatchUrlComplex()
    {
        $route = $this->router->matchUrl('/complex/id-value/name-value');

        $this->assertNotNull($route);

        $this->assertSame(['GET'], $route->getMethods());
        $this->assertSame('/complex/{id}/{name}', $route->getUrl());
        $this->assertSame(\Parable\Tests\TestClasses\Controller::class, $route->getController());
        $this->assertSame('complex', $route->getAction());

        $this->assertNull($route->getCallable());
        $this->assertNull($route->getTemplatePath());

        $this->assertTrue($route->hasParameters());

        $this->assertSame(
            [
                'id'   => 'id-value',
                'name' => 'name-value',
            ],
            $route->getValues()
        );
    }

    public function testmatchUrlCallable()
    {
        $route = $this->router->matchUrl('/callable/stuff');

        $this->assertNotNull($route);

        $this->assertSame(['GET'], $route->getMethods());
        $this->assertSame('/callable/{parameter}', $route->getUrl());
        $this->assertSame('test-file.phtml', $route->getTemplatePath());
        $this->assertSame(
            [
                'parameter' => 'stuff',
            ],
            $route->getValues()
        );

        $this->assertNotNull($route->getCallable());

        $callable = $route->getCallable();

        $parameters = [$route];
        foreach ($route->getValues() as $value) {
            $parameters[] = $value;
        }

        $values = $callable(...$parameters);

        $this->assertSame('stuff', $values[1]);
    }

    public function testMatchNonExistingRoute()
    {
        $this->assertNull($this->router->matchUrl('/non-existent'));
    }

    public function testGetRouteUrlByName()
    {
        $route = $this->router->getRouteUrlByName('complex');
        $this->assertSame("/complex/{id}/{name}", $route);
    }

    public function testGetRouteUrlByNameWithParameters()
    {
        $route = $this->router->getRouteUrlByName('complex', ['id' => 2, 'name' => 'stuff']);
        $this->assertSame("/complex/2/stuff", $route);
    }

    public function testGetRouteUrlByNameNonExistingRoute()
    {
        $this->assertNull($this->router->getRouteUrlByName('non-existent'));
    }

    public function testRouteReturnsNullOnNonExistingValueKey()
    {
        $route = $this->router->matchUrl('/');

        $this->assertInstanceOf(\Parable\Routing\Route::class, $route);

        $this->assertNull($route->getValue('stuff'));
    }

    public function testRouteBuildUrlWithoutParameters()
    {
        $route = $this->router->matchUrl('/');

        $this->assertInstanceOf(\Parable\Routing\Route::class, $route);

        $this->assertSame('/', $route->buildUrlWithParameters([]));
    }

    public function testRouteBuildUrlWithParameters()
    {
        $route = $this->router->matchUrl('/callable/test');

        $this->assertSame("test", $route->getValue("parameter"));

        $this->assertSame("/callable/test", $route->buildUrlWithParameters(["parameter" => "test"]));
    }

    public function testGetRoutesReturnsCorrectNumberOfRoutes()
    {
        $this->assertCount(3, $this->router->getRoutes());
    }

    public function testAddRouteDirectly()
    {
        $router = \Parable\DI\Container::create(\Parable\Routing\Router::class);

        $this->assertCount(0, $router->getRoutes());

        $route = new \Parable\Routing\Route();
        $route->setDataFromArray([
            "methods"  => ["get"],
            "callable" => function () {
                return "test";
            },
        ]);
        $router->addRoute("test", $route);

        $this->assertCount(1, $router->getRoutes());
    }

    public function testAddMultipleRoutesDirectly()
    {
        $router = \Parable\DI\Container::create(\Parable\Routing\Router::class);

        $this->assertCount(0, $router->getRoutes());

        $route1 = new \Parable\Routing\Route();
        $route1->setDataFromArray([
            "methods"  => ["get"],
            "callable" => function () {
                return "test";
            },
        ]);

        $route2 = clone $route1;

        $router->addRoutes([
            "route1" => $route1,
            "route2" => $route2,
        ]);

        $this->assertCount(2, $router->getRoutes());
    }

    public function testAddRouteDirectlyThrowsExceptionOnInvalidRoutePropertiesWithoutMethods()
    {
        $this->expectException(\Parable\Routing\Exception::class);
        $this->expectExceptionMessage("Methods are required and must be passed as an array.");

        $route = new \Parable\Routing\Route();
        $route->setDataFromArray([
            "callable" => function () {
                return "test";
            },
        ]);
        $this->router->addRoute("test", $route);
    }

    public function testAddRouteDirectlyThrowsExceptionOnInvalidRoutePropertiesWithoutControllerActionCallable()
    {
        $this->expectException(\Parable\Routing\Exception::class);
        $this->expectExceptionMessage("Either a controller/action combination or callable is required.");

        $route = new \Parable\Routing\Route();
        $route->setDataFromArray([
            "methods" => ["get"],
        ]);
        $this->router->addRoute("test", $route);
    }

    public function testAddRouteFromArrayReturnsProperRouteObject()
    {
        $this->router->addRouteFromArray("this-is-a-route", [
            "methods" => ["GET"],
            "url" => "/this-is-a-route",
            "controller" => \Parable\Tests\TestClasses\Controller::class,
            "action" => "thisIsARoute",
        ]);
        $route = $this->router->matchUrl("/this-is-a-route");

        $this->assertNotNull($route);

        $this->assertSame("this-is-a-route", $route->getName());
        $this->assertSame(["GET"], $route->getMethods());
        $this->assertSame("/this-is-a-route", $route->getUrl());
        $this->assertSame(\Parable\Tests\TestClasses\Controller::class, $route->getController());
        $this->assertSame("thisIsARoute", $route->getAction());
    }
}
