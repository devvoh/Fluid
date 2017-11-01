<?php

namespace Parable\Tests\Components\Console;

class CommandTest extends \Parable\Tests\Base
{
    /** @var \Parable\Console\Command */
    protected $command;

    protected function setUp()
    {
        parent::setUp();

        $this->command = new \Parable\Console\Command();
    }

    public function testSetGetName()
    {
        $this->command->setName('name');
        $this->assertSame('name', $this->command->getName());
    }

    public function testSetGetDescription()
    {
        $this->command->setDescription('description');
        $this->assertSame('description', $this->command->getDescription());
    }

    public function testSetGetCallableAndRunCommand()
    {
        $callable = function () {
            return 'Yo!';
        };
        $this->command->setCallable($callable);

        $this->assertSame($callable, $this->command->getCallable());
        $this->assertSame('Yo!', $this->command->run());
    }

    public function testAddOptionAndGetOptions()
    {
        $this->command->addOption('option1', true, true, 'stupid');
        $this->command->addOption('option2', false, false, 'smart');

        $this->assertSame(
            [
                'option1' => [
                    'name'          => 'option1',
                    'required'      => true,
                    'valueRequired' => true,
                    'defaultValue'  => 'stupid',
                ],
                'option2' => [
                    'name'          => 'option2',
                    'required'      => false,
                    'valueRequired' => false,
                    'defaultValue'  => 'smart',
                ],
            ],
            $this->command->getOptions()
        );
    }

    public function testAddArgumentAndGetArguments()
    {
        $this->command->addArgument('option1', true);
        $this->command->addArgument('option2', false, 12);

        // Arguments aren't actually named properly until they've been parsed by Parameter
        $this->assertSame(
            [
                [
                    'name'         => 'option1',
                    'required'     => true,
                    'defaultValue' => null,
                ],
                [
                    'name'         => 'option2',
                    'required'     => false,
                    'defaultValue' => 12,
                ],
            ],
            $this->command->getArguments()
        );
    }

    public function testPrepareAcceptsAndPassesInstancesToCallbackProperly()
    {
        $this->command->prepare(
            \Parable\DI\Container::create(\Parable\Console\App::class),
            \Parable\DI\Container::create(\Parable\Console\Output::class),
            \Parable\DI\Container::create(\Parable\Console\Input::class),
            \Parable\DI\Container::create(\Parable\Console\Parameter::class)
        );
        $this->command->setCallable(function ($app, $output, $input, $parameter) {
            return [$app, $output, $input, $parameter];
        });

        $instances = $this->command->run();

        $this->assertInstanceOf(\Parable\Console\App::class, $instances[0]);
        $this->assertInstanceOf(\Parable\Console\Output::class, $instances[1]);
        $this->assertInstanceOf(\Parable\Console\Input::class, $instances[2]);
        $this->assertInstanceOf(\Parable\Console\Parameter::class, $instances[3]);
    }

    public function testExtendingCommandClassWorks()
    {
        $command = new \Parable\Tests\TestClasses\Command();

        $this->assertSame('testcommand', $command->getName());
        $this->assertSame('This is a test command.', $command->getDescription());
        $this->assertNull($command->getCallable());
        $this->assertSame('OK', $command->run());
    }

    public function testCommandCanCallOtherCommand()
    {
        $command = new \Parable\Tests\TestClasses\CommandCallsCommand();
        $command->prepare(
            \Parable\DI\Container::create(\Parable\Console\App::class),
            \Parable\DI\Container::create(\Parable\Console\Output::class),
            \Parable\DI\Container::create(\Parable\Console\Input::class),
            \Parable\DI\Container::create(\Parable\Console\Parameter::class)
        );

        $this->assertSame('calling-command', $command->getName());
        $this->assertSame('Command returned: OK', $command->run());
    }

    public function testCommandRunWithoutCallableReturnsFalse()
    {
        $command = new \Parable\Console\Command();
        $this->assertFalse($command->run());
    }
}
