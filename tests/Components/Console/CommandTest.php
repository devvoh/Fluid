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
        $this->command->addOption(
            'option1',
            \Parable\Console\Parameter::OPTION_VALUE_REQUIRED,
            'smart'
        );

        $options = $this->command->getOptions();

        $option1 = $options["option1"];

        $this->assertInstanceOf(\Parable\Console\Parameter\Option::class, $option1);
        $this->assertSame("option1", $option1->getName());
        $this->assertTrue($option1->isValueRequired());
        $this->assertSame("smart", $option1->getDefaultValue());
    }

    public function testAddArgumentAndGetArguments()
    {
        $this->command->addArgument('arg1', \Parable\Console\Parameter::PARAMETER_REQUIRED);
        $this->command->addArgument('arg2', \Parable\Console\Parameter::PARAMETER_OPTIONAL, 12);

        $arguments = $this->command->getArguments();

        $argument1 = $arguments[0];
        $argument2 = $arguments[1];

        $this->assertInstanceOf(\Parable\Console\Parameter\Argument::class, $argument1);
        $this->assertSame("arg1", $argument1->getName());
        $this->assertTrue($argument1->isRequired());
        $this->assertSame(null, $argument1->getDefaultValue());

        $this->assertInstanceOf(\Parable\Console\Parameter\Argument::class, $argument2);
        $this->assertSame("arg2", $argument2->getName());
        $this->assertFalse($argument2->isRequired());
        $this->assertSame(12, $argument2->getDefaultValue());
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
        $command = $this->createNewCommand();
        $this->assertFalse($command->run());
    }

    public function testGetUsageWithNothingSetIsEmptyString()
    {
        $command = $this->createNewCommand();
        $this->assertEmpty($command->getUsage());
    }

    public function testGetUsageWithEveryCombination()
    {
        $command = $this->createNewCommand("test-command");
        $command->addOption("opt1", \Parable\Console\Parameter::OPTION_VALUE_OPTIONAL);
        $command->addOption("opt2", \Parable\Console\Parameter::OPTION_VALUE_REQUIRED);
        $command->addArgument("arg1", \Parable\Console\Parameter::PARAMETER_REQUIRED);
        $command->addArgument("arg2", \Parable\Console\Parameter::PARAMETER_OPTIONAL);

        $this->assertSame(
            "test-command arg1 [arg2] [--opt1[=value]] [--opt2=value]",
            $command->getUsage()
        );
    }

    protected function createNewCommand($name = null)
    {
        $command = new \Parable\Console\Command();
        $command->prepare(
            \Parable\DI\Container::create(\Parable\Console\App::class),
            \Parable\DI\Container::create(\Parable\Console\Output::class),
            \Parable\DI\Container::create(\Parable\Console\Input::class),
            \Parable\DI\Container::create(\Parable\Console\Parameter::class)
        );
        if ($name) {
            $command->setName($name);
        }
        return $command;
    }
}
