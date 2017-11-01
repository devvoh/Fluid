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

        $_SERVER["argv"] = [];

        $this->parameter = new \Parable\Console\Parameter();
        \Parable\DI\Container::store($this->parameter);

        $this->app = \Parable\DI\Container::get(\Parable\Console\App::class);

        $this->command1 = new \Parable\Console\Command();
        $this->command1->setName('test1');
        $this->command1->setCallable(function () {
            return 'OK1';
        });
        $this->app->addCommand($this->command1);

        $this->command2 = new \Parable\Console\Command();
        $this->command2->setName('test2');
        $this->command2->setCallable(function () {
            return 'OK2';
        });
        $this->app->addCommand($this->command2);

        $this->app->setDefaultCommand('test1');

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
        $this->app->setDefaultCommand('test1');
        $this->assertSame('OK1', $this->app->run());
    }

    public function testPassCommandOnCommandLineRunsAppropriateCommand()
    {
        /** @var \Parable\Console\App $app */
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

        $app->setDefaultCommand('test1', $defaultCommandOnly);

        // If defaultCommandOnly, OK1/test1 should run, otherwise OK2/test2
        $this->assertSame($defaultCommandOnly ? 'OK1' : 'OK2', $app->run());
    }

    public function testRequiredOptionThrowsExceptionIfMissing()
    {
        $this->expectException(\Parable\Console\Exception::class);
        $this->expectExceptionMessage("Required option '--option' not provided.");

        $command = $this->app->getCommand('test1');

        // Now make the option optional, but the value required
        $command->addOption('option', true);

        $this->app->run();
    }

    public function testOptionalOptionWithRequiredValueThrowsExceptionIfNoValue()
    {
        // First test the regular app instance, showing it does not care if the option isn't there
        $this->command1->addOption('option', false, true);
        $this->assertSame('OK1', $this->app->run());

        // And now build a new app with the option passed without a value
        $_SERVER["argv"] = ['./test.php', '--option'];
        $app = \Parable\DI\Container::createAll(\Parable\Console\App::class);
        $app->addCommand($this->command1);

        $app->setDefaultCommand('test1');

        $this->expectException(\Parable\Console\Exception::class);
        $this->expectExceptionMessage("Option '--option' requires a value, which is not provided.");

        $app->run();
    }

    public function testOptionWithValuePassedWorksProperly()
    {
        $_SERVER["argv"] = ['./test.php', '--option', 'passed value here!'];
        $app = \Parable\DI\Container::createAll(\Parable\Console\App::class);
        $this->commandReturnOptionValue->addOption('option', false, false, 'default value is here!');
        $app->addCommand($this->commandReturnOptionValue);

        $app->setDefaultCommand('returnOptionValue');

        $this->assertSame('passed value here!', $app->run());
    }

    public function testOptionWithDefaultValueWorksProperly()
    {
        $_SERVER["argv"] = ['./test.php', '--option'];
        $app = \Parable\DI\Container::createAll(\Parable\Console\App::class);
        $this->commandReturnOptionValue->addOption('option', false, false, 'default value is here!');
        $app->addCommand($this->commandReturnOptionValue);

        $app->setDefaultCommand('returnOptionValue');

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
