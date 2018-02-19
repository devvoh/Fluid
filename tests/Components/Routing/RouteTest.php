<?php

namespace Parable\Tests\Components\Routing;

class RouteTest extends \Parable\Tests\Base
{
    public function testSetGetUrl()
    {
        $route = new \Parable\Routing\Route();
        $route->setUrl("stuff");

        $this->assertSame("/stuff", $route->getUrl());
    }

    public function testCallableMethods()
    {
        $route = new \Parable\Routing\Route();
        $this->assertFalse($route->hasCallable());

        $route->setCallable(function () {
        });

        $this->assertTrue($route->hasCallable());

        $this->assertTrue(is_callable($route->getCallable()));
    }

    public function testTemplatePathMethods()
    {
        $route = new \Parable\Routing\Route();
        $this->assertFalse($route->hasTemplatePath());

        $route->setTemplatePath("location/file.phtml");
        $this->assertTrue($route->hasTemplatePath());

        $this->assertSame("location/file.phtml", $route->getTemplatePath());
    }

    public function testSetDataFromArray()
    {
        $route = new \Parable\Routing\Route();
        $route->setDataFromArray([
            "methods" => ["GET"],
            "controller" => "stuff",
            "action" => "index"
        ]);
        $this->assertTrue($route->hasControllerAndAction());
        $this->assertSame(["GET"], $route->getMethods());
    }

    public function testSetDataFromArrayThrowsExceptionOnInvalidProperty()
    {
        $this->expectException(\Parable\Routing\Exception::class);
        $this->expectExceptionMessage("Tried to set non-existing property 'naww' with value 'index' on Route.");
        $route = new \Parable\Routing\Route();
        $route->setDataFromArray([
            "methods" => ["get"],
            "controller" => "stuff",
            "naww" => "index"
        ]);
    }

    public function testSetMethodsMakesAllUppercase()
    {
        $route = new \Parable\Routing\Route();
        $route->setMethods(["get", "pOsT", "OPTIoNS"]);

        $this->assertSame(["GET", "POST", "OPTIONS"], $route->getMethods());
    }

    public function testMethodIsAcceptedProperly()
    {
        $route = new \Parable\Routing\Route();
        $route->setMethods(["get"]);

        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->assertFalse($route->isAcceptedRequestMethod());

        $_SERVER["REQUEST_METHOD"] = "GET";
        $this->assertTrue($route->isAcceptedRequestMethod());

        unset($_SERVER["REQUEST_METHOD"]);
    }

    public function testSetGetValue()
    {
        $route = new \Parable\Routing\Route();
        $route->setValue("stuff", "what");

        $this->assertSame("what", $route->getValue("stuff"));
    }

    public function testSetGetValues()
    {
        $route = new \Parable\Routing\Route();
        $route->setValues([
            "stuff" => "what",
            "what"  => "stuff",
        ]);

        $this->assertSame(
            [
                "stuff" => "what",
                "what"  => "stuff",
            ],
            $route->getValues()
        );
    }
}
