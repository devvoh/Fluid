<?php

namespace Parable\Tests\Components\Framework;

class AutoloaderTest extends \Parable\Tests\Components\Framework\Base
{
    /** @var \Parable\Framework\Autoloader */
    protected $autoloader;

    protected function setUp()
    {
        parent::setUp();

        $this->autoloader = \Parable\DI\Container::create(\Parable\Framework\Autoloader::class);
    }

    public function testRegister()
    {
        $autoloaders = spl_autoload_functions();

        $this->assertInstanceOf(\Composer\Autoload\ClassLoader::class, $autoloaders[0][0]);
        $this->assertInstanceOf(\Parable\Framework\Autoloader::class, $autoloaders[1][0]);
    }

    public function testAddAndGetLocation()
    {
        $this->assertEmpty($this->autoloader->getLocations());

        $this->autoloader->addLocation("thislocationwasaddedmanually");

        $locations = $this->autoloader->getLocations();
        $this->assertCount(1, $locations);
        $this->assertSame("thislocationwasaddedmanually", $locations[0]);
    }

    public function testLoadFromSetLocation()
    {
        $this->autoloader->addLocation($this->testPath->getDir("tests/TestClasses"));

        $this->assertTrue($this->autoloader->load(\Autoloader\Basic::class));
        $this->assertTrue(class_exists(\Autoloader\Basic::class));
    }
}
