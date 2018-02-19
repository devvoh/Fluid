<?php

namespace Parable\Tests\Components\Event;

class DockTest extends \Parable\Tests\Base
{
    /** @var \Parable\Event\Dock */
    protected $dock;

    protected function setUp()
    {
        parent::setUp();

        $this->dock = \Parable\DI\Container::create(\Parable\Event\Dock::class);
    }

    public function testTriggerWithoutAnythingReturnsDock()
    {
        $this->assertSame($this->dock, $this->dock->trigger('event_that_does_not_exist'));
    }

    public function testTriggerGlobalReturnsDock()
    {
        $this->assertSame($this->dock, $this->dock->trigger('*'));
    }

    public function testIntoAddsCallableAndTemplate()
    {
        $this->dock->into('test_dock_into', function ($event, &$payload) {
        }, $this->testPath->getDir('tests/TestTemplates/dock_test_template.phtml'));

        $docks = $this->liberateProperty($this->dock, 'docks');
        $this->assertCount(1, $docks);
        $this->assertArrayHasKey('test_dock_into', $docks);

        $testDock = $docks['test_dock_into'][0];

        $this->assertArrayHasKey('callable', $testDock);
        $this->assertArrayHasKey('templatePath', $testDock);

        $callable = $testDock['callable'];
        $template = $testDock['templatePath'];

        $this->assertNotEmpty($callable);
        $this->assertNotEmpty($template);

        $this->assertTrue(is_callable($callable));
    }

    public function testIntoAndTriggerBasicWithStringPayload()
    {
        $this->dock->into('test_dock_into', function ($event, &$payload) {
            $this->assertSame('test_dock_into', $event);
            $payload .= ", world!";
        });

        $payload = "Hello";
        $this->dock->trigger('test_dock_into', $payload);

        $this->assertSame("Hello, world!", $payload);
    }

    public function testTriggerCanTriggerMultipleHooks()
    {
        $this->dock->into('test_dock_into', function ($event, &$payload) {
            $payload .= '1';
        });
        $this->dock->into('test_dock_into', function ($event, &$payload) {
            $payload .= '2';
        });
        $this->dock->into('test_dock_into', function ($event, &$payload) {
            $payload .= '3!';
        });

        $payload = 'Testing... ';
        $this->dock->trigger('test_dock_into', $payload);

        $this->assertSame('Testing... 123!', $payload);
    }

    public function testGlobalIntoRespondsToAllTriggers()
    {
        $this->dock->into('*', function ($event, &$payload) {
            $payload .= ".{$event}";
        });

        $payload = "Triggering";
        $this->dock->trigger('random_1', $payload);
        $this->dock->trigger('random_2', $payload);
        $this->dock->trigger('random_3', $payload);

        $this->assertSame('Triggering.random_1.random_2.random_3', $payload);
    }

    public function testTemplateFileOutputShownProperly()
    {
        $this->expectOutputString("Hello, world!");

        $this->dock->into('test_dock_into', function () {
            echo "Hello";
        }, $this->testPath->getDir('tests/TestTemplates/dock_test_template.phtml'));

        $this->dock->trigger('test_dock_into');
    }
}
