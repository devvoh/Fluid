<?php

namespace Parable\Tests\Components\Console\Command;

class HelpTest extends \Parable\Tests\Base
{
    /** @var \Parable\Console\App */
    protected $app;

    /** @var \Parable\Console\Parameter */
    protected $parameter;

    /** @var \Parable\Console\Command\Help */
    protected $helpCommand;

    protected function setUp()
    {
        parent::setUp();

        $this->app         = \Parable\DI\Container::create(\Parable\Console\App::class);
        $this->parameter   = \Parable\DI\Container::create(\Parable\Console\Parameter::class);

        $this->helpCommand = new \Parable\Console\Command\Help();
        $this->app->addCommand($this->helpCommand);

        $this->app->setName("Help Test App");

        $this->helpCommand->prepare(
            $this->app,
            \Parable\DI\Container::create(\Parable\Console\Output::class),
            \Parable\DI\Container::create(\Parable\Console\Input::class),
            $this->parameter
        );
    }

    public function testRunListsAvailableCommandsAndDescription()
    {
        $this->helpCommand->run();

        $content = $this->getActualOutputAndClean();

        $this->assertContains("Help Test App", $content);
        $this->assertContains("Available commands:", $content);
        $this->assertContains("help", $content);
        $this->assertContains("Shows all commands available.", $content);
    }

    public function testHelpOnSpecificCommandReturnsDescriptionAndUsage()
    {
        $this->parameter->setCommandArguments($this->helpCommand->getArguments());
        $this->parameter->setParameters([
            './test.php',
            'help',
            'help',
        ]);
        $this->parameter->checkCommandArguments();

        $this->helpCommand->run();

        $content = $this->getActualOutputAndClean();

        $this->assertContains("Help Test App", $content);
        $this->assertContains("Description:", $content);
        $this->assertContains("Usage:", $content);
    }

    public function testHelpOnUnknownCommandReturnsError()
    {
        $this->parameter->setCommandArguments($this->helpCommand->getArguments());
        $this->parameter->setParameters([
            './test.php',
            'help',
            'what-is-this-i-cant-even',
        ]);
        $this->parameter->checkCommandArguments();

        $this->helpCommand->run();

        $content = $this->getActualOutputAndClean();

        $this->assertContains("Unknown command:", $content);
        $this->assertContains("what-is-this-i-cant-even", $content);
    }
}
