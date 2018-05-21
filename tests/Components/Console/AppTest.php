<?php

namespace Parable\Tests\Components\Console;

class AppTest extends \Parable\Tests\Base
{
    /** @var \Parable\Console\Parameter */
    protected $parameter;

    /** @var \Parable\Console\App */
    protected $app;

    /** @var \Parable\Console\Command */
    protected $command1;

    /** @var \Parable\Console\Command */
    protected $command2;

    /** @var \Parable\Console\Command */
    protected $commandReturnOptionValue;

    protected function setUp()
    {
        parent::setUp();

        $this->parameter = new \Parable\Console\Parameter();
        \Parable\DI\Container::store($this->parameter);

        $this->app = \Parable\DI\Container::get(\Parable\Console\App::class);

        $this->command1 = new \Parable\Console\Command();
        $this->command1->setName('test1');
        $this->command1->addArgument("arg1");
        $this->command1->setCallable(function () {
            return 'OK1';
        });
        $this->app->addCommand($this->command1);

        $this->command2 = new \Parable\Console\Command();
        $this->command2->setName('test2');
        $this->command1->addArgument("arg1");
        $this->command2->setCallable(function () {
            return 'OK2';
        });
        $this->app->addCommand($this->command2);

        $this->app->setDefaultCommand($this->command1);

        $this->commandReturnOptionValue = new \Parable\Console\Command();
        $this->commandReturnOptionValue->setName('returnOptionValue');
        $this->commandReturnOptionValue->setCallable(function (
            \Parable\Console\App $app,
            \Parable\Console\Output $output,
            \Parable\Console\Input $input,
            \Parable\Console\Parameter $parameter
        ) {
            return $parameter->getOption('option');
        });

        $this->app->addCommand($this->commandReturnOptionValue);
    }

    public function testAddCommands()
    {
        $app = \Parable\DI\Container::createAll(\Parable\Console\App::class);
        $this->assertCount(0, $app->getCommands());

        $app->addCommands([
            $this->command1,
            $this->command2,
        ]);

        $this->assertCount(2, $app->getCommands());
    }

    public function testAppSetGetName()
    {
        $this->app->setName('Super-application');
        $this->assertSame('Super-application', $this->app->getName());
    }

    public function testAppAddGetCommand()
    {
        $commandGot = $this->app->getCommand('test1');

        $this->assertSame('test1', $commandGot->getName());
        $this->assertSame('OK1', $commandGot->run());

        $commandGot = $this->app->getCommand('test2');

        $this->assertSame('test2', $commandGot->getName());
        $this->assertSame('OK2', $commandGot->run());
    }

    public function testHasCommand()
    {
        $this->assertTrue($this->app->hasCommand('test1'));
        $this->assertFalse($this->app->hasCommand('nope not this one'));
    }

    public function testAppGetCommandsReturnsAll()
    {
        $commands = $this->app->getCommands();

        $this->assertSame('test1', $commands['test1']->getName());
        $this->assertSame('OK1', $commands['test1']->run());

        $this->assertSame('test2', $commands['test2']->getName());
        $this->assertSame('OK2', $commands['test2']->run());
    }

    public function testAppGetCommandsWithoutCommandsReturnsEmptyArray()
    {
        $app = \Parable\DI\Container::create(\Parable\Console\App::class);
        $this->assertSame([], $app->getCommands());
    }

    public function testAppGetNonExistingCommandReturnsNull()
    {
        $app = \Parable\DI\Container::create(\Parable\Console\App::class);
        $this->assertNull($app->getCommand('nope'));
    }

    public function testSetDefaultCommandRunsDefaultCommand()
    {
        $this->app->setDefaultCommand($this->command1);
        $this->assertSame('OK1', $this->app->run());
    }

    public function testSetDefaultCommandByNameRunsDefaultCommand()
    {
        $this->app->setDefaultCommandByName("test1");
        $this->assertSame('OK1', $this->app->run());
    }

    public function testPassCommandOnCommandLineRunsAppropriateCommand()
    {
        $app = new \Parable\Console\App(new \Parable\Console\Output(), new \Parable\Console\Input(), $this->parameter);
        $app->addCommand($this->command1);
        $app->addCommand($this->command2);

        // Same as calling 'php test.php test2'
        $this->parameter->setParameters(['./test.php', 'test2']);

        $this->assertSame("OK2", $app->run());

        // Same as calling 'php test.php test2'
        $this->parameter->setParameters(['./test.php', 'test1']);

        $this->assertSame("OK1", $app->run());
    }

    public function testRemoveCommandbyName()
    {
        $app = new \Parable\Console\App(new \Parable\Console\Output(), new \Parable\Console\Input(), $this->parameter);
        $app->addCommand($this->command1);
        $app->addCommand($this->command2);

        $this->assertCount(2, $app->getCommands());

        $app->removeCommandByName($this->command1->getName());

        $this->assertCount(1, $app->getCommands());

        $this->assertSame($this->command2, $app->getCommand($this->command2->getName()));
    }

    /**
     * @dataProvider dpTrueFalse
     *
     * @param $defaultCommandOnly
     */
    public function testSetDefaultCommandWithCommandPassedRespectsDefaultOnlyCommand($defaultCommandOnly)
    {
        // Same as calling 'php test.php test2'
        $_SERVER["argv"] = ['./test.php', 'test2'];

        $app = \Parable\DI\Container::createAll(\Parable\Console\App::class);
        $app->addCommand($this->command1);
        $app->addCommand($this->command2);

        $app->setOnlyUseDefaultCommand($defaultCommandOnly);
        $app->setDefaultCommand($this->command1);

        // If defaultCommandOnly, OK1/test1 should run, otherwise OK2/test2
        $this->assertSame($defaultCommandOnly ? 'OK1' : 'OK2', $app->run());

        // If default command only, the "command name" should be shifted to the arguments list instead
        $arguments = $this->command1->getArguments();
        if ($defaultCommandOnly) {
            $this->assertSame("test2", $arguments[0]->getValue());
        } else {
            $this->assertNull($arguments[0]->getValue());
        }
    }

    public function testOptionalOptionWithRequiredValueThrowsExceptionIfNoValue()
    {
        $this->expectException(\Parable\Console\Exception::class);
        $this->expectExceptionMessage("Option '--option' requires a value, which is not provided.");

        // First test the regular app instance, showing it does not care if the option isn't there
        $this->command1->addOption(
            'option',
            \Parable\Console\Parameter::OPTION_VALUE_REQUIRED
        );
        $this->assertSame('OK1', $this->app->run());

        // And now build a new app with the option passed without a value
        $_SERVER["argv"] = ['./test.php', '--option'];
        $app = \Parable\DI\Container::createAll(\Parable\Console\App::class);
        $app->addCommand($this->command1);

        $app->setDefaultCommand($this->command1);

        $app->run();
    }

    public function testOptionWithValuePassedWorksProperly()
    {
        $_SERVER["argv"] = ['./test.php', '--option=passed value here!'];
        $app = \Parable\DI\Container::createAll(\Parable\Console\App::class);
        $this->commandReturnOptionValue->addOption(
            'option',
            \Parable\Console\Parameter::OPTION_VALUE_OPTIONAL,
            'default value is here!'
        );
        $app->addCommand($this->commandReturnOptionValue);

        $app->setDefaultCommand($this->commandReturnOptionValue);

        $this->assertSame('passed value here!', $app->run());
    }

    public function testOptionWithDefaultValueWorksProperly()
    {
        $_SERVER["argv"] = ['./test.php', '--option'];
        $app = \Parable\DI\Container::createAll(\Parable\Console\App::class);
        $this->commandReturnOptionValue->addOption(
            'option',
            \Parable\Console\Parameter::OPTION_VALUE_OPTIONAL,
            'default value is here!'
        );
        $app->addCommand($this->commandReturnOptionValue);

        $app->setDefaultCommand($this->commandReturnOptionValue);

        $this->assertSame('default value is here!', $app->run());
    }

    public function testThrowsExceptionWhenRanWithoutCommand()
    {
        $this->expectExceptionMessage("No valid commands found.");
        $this->expectException(\Parable\Console\Exception::class);

        $app = new \Parable\Console\App(
            new \Parable\Console\Output(),
            new \Parable\Console\Input(),
            new \Parable\Console\Parameter()
        );
        $app->run();
    }
}
