<?php

namespace Parable\Tests\Components\Event;

class DockTest extends \Parable\Tests\Base
{
    /** @var \Parable\Event\Dock */
    protected $dock;

    /** @var \Parable\Filesystem\Path */
    protected $path;

    protected function setUp()
    {
        parent::setUp();

        $this->dock = $this->di->create(\Parable\Event\Dock::class);
        $this->path = $this->di->get(\Parable\Filesystem\Path::class);
    }

    public function testIntoAndTriggerBasicWithStringPayload()
    {
        $this->dock->into('test_dock_into', function ($event, &$string) {
            $this->assertSame('test_dock_into', $event);
            $string .= ", world!";
        });

        $payload = "Hello";
        $this->dock->trigger('test_dock_into', $payload);

        $this->assertSame("Hello, world!", $payload);
    }

    public function testTemplateFileOutputShownProperly()
    {
        $this->expectOutputString("Hello, world!");

        $this->dock->into('test_dock_into', function () {
            echo "Hello";
        }, $this->path->getDir('tests/TestTemplates/dock_test_template.phtml'));

        $this->dock->trigger('test_dock_into');
    }
}
