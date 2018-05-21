<?php

namespace Parable\Tests\Components\Framework;

class CommandLoaderTest extends \Parable\Tests\Components\Framework\Base
{
    /** @var \Parable\Console\App */
    protected $app;

    /** @var \Parable\Framework\Loader\CommandLoader */
    protected $commandLoader;

    public function setUp()
    {
        parent::setUp();

        $this->app           = \Parable\DI\Container::get(\Parable\Console\App::class);
        $this->commandLoader = \Parable\DI\Container::get(\Parable\Framework\Loader\CommandLoader::class);
    }

    public function testLoad()
    {
        $this->assertCount(0, $this->app->getCommands());

        $command = \Parable\DI\Container::create(\Parable\Tests\TestClasses\Command::class);

        $this->commandLoader->load([
            get_class($command)
        ]);

        $this->assertCount(1, $this->app->getCommands());
        $this->assertInstanceOf(
            \Parable\Tests\TestClasses\Command::class,
            $this->app->getCommand($command->getName())
        );
        $this->assertInstanceOf(
            \Parable\Console\Command::class,
            $this->app->getCommand($command->getName())
        );
    }
}
