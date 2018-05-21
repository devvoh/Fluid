<?php

namespace Parable\Tests\Components\Framework;

class PackageManagerTest extends \Parable\Tests\Components\Framework\Base
{
    /** @var \Parable\Console\App */
    protected $app;

    /** @var \Parable\Framework\Package\PackageManager */
    protected $packageManager;

    public function setUp()
    {
        parent::setUp();

        $this->app            = \Parable\DI\Container::get(\Parable\Console\App::class);
        $this->packageManager = \Parable\DI\Container::get(\Parable\Framework\Package\PackageManager::class);
    }

    public function testAddPackage()
    {
        $this->packageManager->addPackage(\Parable\Tests\TestClasses\PackageTest::class);

        $this->assertSame(
            [\Parable\Tests\TestClasses\PackageTest::class],
            $this->liberateProperty($this->packageManager, "packages")
        );
    }

    public function testRegister()
    {
        // There should be no commands
        $this->assertCount(0, $this->app->getCommands());

        $this->packageManager->addPackage(\Parable\Tests\TestClasses\PackageTest::class);
        $this->packageManager->registerPackages();

        // There should now be one command
        $this->assertCount(1, $this->app->getCommands());
        $this->assertInstanceOf(
            \Parable\Tests\TestClasses\Command::class,
            $this->app->getCommand("testcommand")
        );
        $this->assertInstanceOf(
            \Parable\Console\Command::class,
            $this->app->getCommand("testcommand")
        );

        // And the init echoes something
        $this->assertSame("This init was loaded.", $this->getActualOutputAndClean());
    }

    public function testRegisterEmptyPackageDoesNothing()
    {
        // There should be no commands
        $this->assertCount(0, $this->app->getCommands());

        $this->packageManager->addPackage(\Parable\Tests\TestClasses\PackageTestEmpty::class);
        $this->packageManager->registerPackages();

        // There should now still have no commands
        $this->assertCount(0, $this->app->getCommands());
    }
}
