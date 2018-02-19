<?php

namespace Parable\Tests\Components\Framework;

class AppTest extends \Parable\Tests\Components\Framework\Base
{
    /** @var \Parable\Framework\App */
    protected $app;

    /** @var array */
    protected $config = [];

    /** @var \Parable\Routing\Router */
    protected $router;

    /** @var \Parable\Http\Response|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockResponse;

    /** @var \Parable\GetSet\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockSession;

    /** @var bool */
    protected $noRoutesFoundTriggered = false;

    protected function setUp()
    {
        parent::setUp();

        /** @var \Parable\Http\Response $this->mockResponse */
        // Response should not actually terminate
        $this->mockResponse = $this->createPartialMock(\Parable\Http\Response::class, ['terminate']);
        $this->mockResponse->__construct(\Parable\DI\Container::get(\Parable\Http\Request::class));

        \Parable\DI\Container::store($this->mockResponse, \Parable\Http\Response::class);

        /** @var \Parable\GetSet\Session $this->mockSession */
        // Fake the session since we need to take out the starting of the session
        $this->mockSession = $this->createPartialMock(\Parable\GetSet\Session::class, ['start']);
        $this->mockSession->expects($this->any())->method('start')->willReturn($this->mockSession);

        \Parable\DI\Container::store($this->mockSession, \Parable\GetSet\Session::class);

        $this->router = \Parable\DI\Container::get(\Parable\Routing\Router::class);
        $this->router->addRouteFromArray('index', [
            'methods' => ['GET'],
            'url' => '/',
            'controller' => \Parable\Tests\TestClasses\Controller::class,
            'action' => 'index',
            'templatePath' => $this->testPath->getDir('tests/TestTemplates/index.phtml'),
        ]);

        $this->app = $this->createApp();
    }

    public function testAppRun()
    {
        $this->mockResponse->expects($this->once())->method('terminate');
        $this->app->run();

        $output = $this->getActualOutputAndClean();

        $this->assertContains('PHP framework by <a href="http://devvoh.com">devvoh</a>', $output);
        $this->assertSame(200, $this->mockResponse->getHttpCode());
        $this->assertSame('OK', $this->mockResponse->getHttpCodeText());
    }

    public function testAppRunWithoutRoutesTriggersHookNoRoutesFound()
    {
        $this->assertFalse($this->noRoutesFoundTriggered);

        $hook = \Parable\DI\Container::get(\Parable\Event\Hook::class);
        $hook->into(\Parable\Framework\App::HOOK_LOAD_ROUTES_NO_ROUTES_FOUND, function ($event) {
            $this->assertSame(\Parable\Framework\App::HOOK_LOAD_ROUTES_NO_ROUTES_FOUND, $event);
            $this->noRoutesFoundTriggered = true;
        });

        $app = $this->createApp(\Parable\Tests\TestClasses\Config\TestNoRouting::class);
        $app->run();

        $this->getActualOutputAndClean();

        $this->assertTrue($this->noRoutesFoundTriggered);
    }

    public function testAppRunWithUnknownUrlGives404()
    {
        $_GET['url'] = '/simple';

        $this->app->run();

        $this->getActualOutputAndClean();

        $this->assertSame(404, $this->mockResponse->getHttpCode());
        $this->assertSame("Not Found", $this->mockResponse->getHttpCodeText());
    }

    public function testAppRunWithSimpleUrlWorks()
    {
        $_GET['url'] = '/simple';
        $this->router->addRouteFromArray(
            'simple',
            [
                'methods' => ['GET'],
                'url' => '/simple',
                'callable' => function () {
                    echo "callable route found";
                }
            ]
        );

        $this->app->run();

        $output = $this->getActualOutputAndClean();

        $this->assertSame('callable route found', $output);
    }

    public function testAppWithAnyQuickRoute()
    {
        $_GET['url'] = '/any';
        $this->app->get("any", function () {
            return "any quickroute";
        })->run();
        $this->assertSame("any quickroute", $this->getActualOutputAndClean());
    }

    public function testAddQuickRouteWithoutNameGeneratesUniqueID()
    {
        $_GET['url'] = '/any';
        $this->app->get("any", function () {
            return "any quickroute";
        })->run();

        $dispatcher = \Parable\DI\Container::get(\Parable\Framework\Dispatcher::class);
        $dispatchedRoute = $dispatcher->getDispatchedRoute();

        $this->getActualOutputAndClean();

        $this->assertSame(23, strlen($dispatchedRoute->getName()));
        $this->assertContains(".", $dispatchedRoute->getName());
    }

    public function testAddQuickRouteWithNameActuallyHasName()
    {
        $_GET['url'] = '/any';
        $this->app->get("any", function () {
            return "any quickroute";
        }, "this-is-a-named-route")->run();

        $dispatcher = \Parable\DI\Container::get(\Parable\Framework\Dispatcher::class);
        $dispatchedRoute = $dispatcher->getDispatchedRoute();

        $this->getActualOutputAndClean();

        $this->assertSame("this-is-a-named-route", $dispatchedRoute->getName());
    }

    public function testAppWithAnyQuickRouteAcceptsControllerActionCombination()
    {
        $_GET['url'] = '/any-controller-action';
        $this->app->get(
            "any-controller-action",
            [\Parable\Tests\TestClasses\Controller::class, "simple"]
        )->run();
        $this->assertSame("simple action", $this->getActualOutputAndClean());

        $dispatcher = \Parable\DI\Container::get(\Parable\Framework\Dispatcher::class);
        $dispatchedRoute = $dispatcher->getDispatchedRoute();

        $this->assertSame(\Parable\Tests\TestClasses\Controller::class, $dispatchedRoute->getController());
        $this->assertSame("simple", $dispatchedRoute->getAction());
        $this->assertNull($dispatchedRoute->getCallable());
    }

    public function testAppWithAnyQuickRouteAcceptsStaticControllerActionAsCallable()
    {
        $_GET['url'] = '/any-static-callable';
        $this->app->get(
            "any-static-callable",
            [\Parable\Tests\TestClasses\Controller::class, "staticIndex"]
        )->run();
        $this->assertSame("static index here!", $this->getActualOutputAndClean());

        $dispatcher = \Parable\DI\Container::get(\Parable\Framework\Dispatcher::class);
        $dispatchedRoute = $dispatcher->getDispatchedRoute();

        $this->assertNull($dispatchedRoute->getController());
        $this->assertNull($dispatchedRoute->getAction());
        $this->assertSame(
            [\Parable\Tests\TestClasses\Controller::class, "staticIndex"],
            $dispatchedRoute->getCallable()
        );
    }

    /**
     * @param $type
     *
     * @dataProvider dpMethods
     */
    public function testAppWithSpecificQuickRoute($type)
    {
        $_SERVER["REQUEST_METHOD"] = strtoupper($type);
        $_GET['url'] = '/quickroute';
        $this->app->{$type}("quickroute", function () use ($type) {
            return "{$type} quickroute";
        })->run();
        $this->assertSame("{$type} quickroute", $this->getActualOutputAndClean());
    }

    /**
     * @param $type
     *
     * @dataProvider dpMethods
     */
    public function testAppWithAnyQuickRouteAcceptsAnyMethod($type)
    {
        $_SERVER["REQUEST_METHOD"] = strtoupper($type);
        $_GET['url'] = '/quickroute';
        $this->app->any("quickroute", function () use ($type) {
            return "any quickroute";
        })->run();
        $this->assertSame("any quickroute", $this->getActualOutputAndClean());
    }

    /**
     * @param $type
     *
     * @dataProvider dpMethods
     */
    public function testAppWithMultipleQuickRouteAcceptsMultipleMethods($type)
    {
        $_SERVER["REQUEST_METHOD"] = strtoupper($type);
        $_GET['url'] = '/quickroute';
        $this->app->multiple(["GET", "PUT", "OPTIONS"], "quickroute", function () use ($type) {
            return "multiple quickroute";
        })->run();

        if (in_array(strtoupper($type), ["GET", "PUT", "OPTIONS"])) {
            $this->assertSame("multiple quickroute", $this->getActualOutputAndClean());
        } else {
            $this->assertEmpty($this->getActualOutputAndClean());
        }
    }

    public function dpMethods()
    {
        return [
            ["get"],
            ["post"],
            ["put"],
            ["patch"],
            ["delete"],
            ["options"],
        ];
    }

    public function testAppWithGetQuickRouteDoesNotAcceptPost()
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_GET['url'] = '/quickroute';
        $this->app->get("quickroute", function () {
            return "get quickroute";
        })->run();
        // And now it should be empty
        $this->assertSame("", $this->getActualOutputAndClean());

        $this->assertSame(404, $this->mockResponse->getHttpCode());
    }

    public function testAppRunWithTemplatedUrlWorks()
    {
        $_GET['url'] = '/template';
        $this->router->addRouteFromArray(
            'template',
            [
                'methods' => ['GET'],
                'url' => '/template',
                'callable' => function () {
                    echo "Hello";
                },
                'templatePath' => $this->testPath->getDir('tests/TestTemplates/app_test_template.phtml'),
            ]
        );

        $this->app->run();

        $output = $this->getActualOutputAndClean();

        $this->assertSame('Hello, world!', $output);
    }

    public function testLoadRoutesThrowsExceptionWhenInvalidRouteIsAdded()
    {
        $this->expectException(\Parable\Routing\Exception::class);
        $this->expectExceptionMessage("Either a controller/action combination or callable is required.");

        $this->router->addRouteFromArray(
            'simple',
            [
                'methods' => ['GET'],
                'url' => '/simple',
            ]
        );

        $this->app->run();
    }

    public function testAppRunWithValuedUrlWorks()
    {
        $_GET['url'] = '/valued/985';
        $this->router->addRouteFromArray(
            'valued',
            [
                'methods' => ['GET'],
                'url' => '/valued/{id}',
                'callable' => function ($id) {
                    echo "callable route found with id: {$id}";
                }
            ]
        );

        $this->app->run();

        $output = $this->getActualOutputAndClean();

        $this->assertSame('callable route found with id: 985', $output);
    }

    public function testAppThrowsExceptionOnInvalidRoute()
    {
        $this->expectException(\Parable\Routing\Exception::class);
        $this->expectExceptionMessage("Either a controller/action combination or callable is required.");

        $this->router->addRouteFromArray('simple', [
            'methods' => ['GET'],
            'url' => '/',
        ]);

        $app = $this->createApp();
        $app->run();

        // And reset
        \Parable\DI\Container::clear(\Routing\App::class);
    }

    public function testAppThrowsExceptionOnWrongRouteInterface()
    {
        $this->expectException(\Parable\Framework\Exception::class);
        $this->expectExceptionMessage("Routing\Wrong does not extend \Parable\Framework\Routing\AbstractRouting");

        $app = $this->createApp(\Parable\Tests\TestClasses\Config\TestBrokenRouting::class);
        $app->run();
    }

    public function testEnableErrorReporting()
    {
        // get the original state
        $errorReportingEnabledOriginally = $this->app->isErrorReportingEnabled();

        $this->app->setErrorReportingEnabled(true);

        $this->assertSame("1", ini_get('display_errors'));
        $this->assertSame(E_ALL, error_reporting());

        $this->app->setErrorReportingEnabled(false);

        $this->assertSame("0", ini_get('display_errors'));
        $this->assertSame(E_ALL | ~E_DEPRECATED, error_reporting());

        // and reset to the original state
        $this->app->setErrorReportingEnabled($errorReportingEnabledOriginally);
    }

    public function testDebugConfigOptionEnablesErrorReporting()
    {
        $config = \Parable\DI\Container::create(\Parable\Tests\TestClasses\SettableConfig::class);
        $config->set([
            "parable" => [
                "debug" => true,
            ],
        ]);

        $app = $this->createAppWithSpecificConfig($config);

        $this->assertFalse($app->isErrorReportingEnabled());

        $app->run();

        $this->assertTrue($app->isErrorReportingEnabled());
    }

    public function testSetDefaultTimezoneFromConfig()
    {
        // get the original state
        $timezone = date_default_timezone_get();

        $config = \Parable\DI\Container::create(\Parable\Tests\TestClasses\SettableConfig::class);
        $config->set([
            "parable" => [
                "timezone" => "Antarctica/McMurdo",
            ],
        ]);

        $app = $this->createAppWithSpecificConfig($config);

        $this->assertSame($timezone, date_default_timezone_get());

        $app->run();

        $this->assertSame("Antarctica/McMurdo", date_default_timezone_get());

        // and reset to the original state
        date_default_timezone_set($timezone);
    }

    /**
     * @param string $mainConfigClassName
     * @return \Parable\Framework\App
     */
    protected function createApp($mainConfigClassName = \Parable\Tests\TestClasses\Config\Test::class)
    {
        $config = new \Parable\Framework\Config($this->testPath);
        $config->setMainConfigClassName($mainConfigClassName);

        \Parable\DI\Container::store($config);

        $app = \Parable\DI\Container::create(\Parable\Framework\App::class);

        \Parable\DI\Container::clear(\Parable\Framework\Config::class);

        return $app;
    }

    protected function createAppWithSpecificConfig(\Parable\Framework\Interfaces\Config $specificConfig)
    {
        $config = new \Parable\Framework\Config($this->testPath);
        $config->setMainConfigClassName(get_class($specificConfig));
        $config->addConfig($specificConfig);

        \Parable\DI\Container::store($config);

        $app = \Parable\DI\Container::create(\Parable\Framework\App::class);

        \Parable\DI\Container::clear(\Parable\Framework\Config::class);

        return $app;
    }

    public function tearDown()
    {
        // Make sure we never echo nothin'
        $this->getActualOutputAndClean();
        parent::tearDown();
    }
}
