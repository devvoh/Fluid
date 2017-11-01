<?php

namespace Parable\Tests\Components\Console;

class ParameterTest extends \Parable\Tests\Base
{
    /** @var \Parable\Console\Parameter */
    protected $parameter;

    protected function setUp()
    {
        parent::setUp();

        $this->parameter = new \Parable\Console\Parameter();
    }

    public function testParseParametersWorkedCorrectly()
    {
        $this->parameter->setParameters([
            './test.php',
            'command-to-run',
            '--option',
            '--key',
            'value',
        ]);

        $this->assertSame('./test.php', $this->parameter->getScriptName());
        $this->assertSame('command-to-run', $this->parameter->getCommandName());

        $this->assertTrue($this->parameter->getOption('option'));
        $this->assertSame("value", $this->parameter->getOption('key'));

        $this->assertSame(
            [
                "--option",
                "--key",
                "value",
            ],
            $this->parameter->getParameters()
        );
    }

    public function testCommandNameIsReturnedProperlyIfGiven()
    {
        $this->parameter->setParameters([
            './test.php',
            'command-to-run',
        ]);

        $this->assertSame('./test.php', $this->parameter->getScriptName());
        $this->assertSame('command-to-run', $this->parameter->getCommandName());
    }

    public function testGetInvalidOptionReturnsNull()
    {
        $this->assertNull($this->parameter->getOption('la-dee-dah'));
    }

    public function testCommandNameIsNullIfNotGiven()
    {
        $this->parameter->setParameters([
            './test.php',
        ]);

        $this->assertSame('./test.php', $this->parameter->getScriptName());
        $this->assertNull($this->parameter->getCommandName());
    }

    public function testCommandNameIsNullIfNotGivenButThereIsAnOptionGiven()
    {
        $this->parameter->setParameters([
            './test.php',
            '--option',
        ]);

        $this->assertSame('./test.php', $this->parameter->getScriptName());
        $this->assertNull($this->parameter->getCommandName());
    }

    public function testThrowsExceptionWhenOptionIsGivenButValueRequiredNotGiven()
    {
        $this->parameter->setParameters([
            './test.php',
            'command-to-run',
            '--option',
        ]);

        $this->expectException(\Parable\Console\Exception::class);
        $this->expectExceptionMessage("Option '--option' requires a value, which is not provided.");

        $this->parameter->setOptions([
            ['name' => 'option', 'required' => false, 'valueRequired' => true],
        ]);
        $this->parameter->checkOptions();
    }

    public function testOptionIsGivenAndValueRequiredAlsoGivenWorksProperly()
    {
        $this->parameter->setParameters([
            './test.php',
            'command-to-run',
            '--option',
            'option-value'
        ]);

        $this->parameter->setOptions([
            ['name' => 'option', 'required' => false, 'valueRequired' => true],
        ]);
        $this->parameter->checkOptions();

        $this->assertSame('option-value', $this->parameter->getOption('option'));
    }

    public function testThrowsExceptionWhenRequiredOptionIsMissing()
    {
        $this->parameter->setParameters([
            './test.php',
            'command-to-run',
        ]);

        $this->expectException(\Parable\Console\Exception::class);
        $this->expectExceptionMessage("Required option '--option' not provided.");
        $this->parameter->setOptions([
            ['name' => 'option', 'required' => true],
        ]);
        $this->parameter->checkOptions();
    }

    public function testOptionAcceptsEqualsValue()
    {
        $this->parameter->setParameters([
            './test.php',
            'command-to-run',
            '--option1=value1',
        ]);

        $this->assertSame("value1", $this->parameter->getOption("option1"));
    }

    public function testRequiredArgumentThrowsException()
    {
        $this->parameter->setParameters([
            './test.php',
            'command-to-run',
            'arg1',
        ]);
        $this->parameter->setArguments([
            ['name' => 'numero1', 'required' => true],
            ['name' => 'numero2', 'required' => true],
        ]);

        $this->expectException(\Parable\Console\Exception::class);
        $this->expectExceptionMessage("Required argument '2:numero2' not provided.");

        $this->parameter->checkArguments();
    }

    public function testGetArgumentReturnsAppropriateValues()
    {
        $this->parameter->setParameters([
            './test.php',
            'command-to-run',
            'arg1',
            'arg2',
        ]);
        $this->parameter->setArguments([
            ['name' => 'numero1', 'required' => true],
            ['name' => 'numero2', 'required' => true, 'defaultValue' => 12],
            ['name' => 'numero3', 'required' => false, 'defaultValue' => 24],
        ]);

        $this->parameter->checkArguments();

        $this->assertSame("arg1", $this->parameter->getArgument("numero1"));
        $this->assertSame("arg2", $this->parameter->getArgument("numero2"));
        $this->assertSame(24, $this->parameter->getArgument("numero3"));
    }

    public function testInvalidArgumentReturnsNull()
    {
        $this->assertNull($this->parameter->getArgument("totally not"));
    }

    public function testMultipleParameters()
    {
        $this->parameter->setParameters([
            './test.php',
            'command-to-run',
            '--option1',
            'value1',
            '--option2',
            '--option3',
            'value3',
        ]);

        $this->parameter->setOptions([
            'option1' => ['name' => 'option1', 'required' => true, 'valueRequired' => true],
            'option2' => ['name' => 'option2', 'required' => true],
            'option3' => ['name' => 'option3', 'required' => true, 'valueRequired' => true],
        ]);

        $this->parameter->checkOptions();

        $this->assertSame(
            [
                'option1' => 'value1',
                'option2' => true,
                'option3' => 'value3',
            ],
            $this->parameter->getOptions()
        );
    }

    public function testArgumentsWorkProperly()
    {
        $this->parameter->setParameters([
            './test.php',
            'command-to-run',
            'argument1',
            'argument2 is a string',
            '--option1',
            'value1',
            'argument3!'
        ]);

        $this->parameter->setOptions([
            'option1' => ['name' => 'option1'],
        ]);
        $this->parameter->setArguments([
            ['name' => 'arg1', 'required' => true],
            ['name' => 'arg2', 'required' => false],
            ['name' => 'arg3', 'required' => false],
        ]);

        $this->parameter->checkOptions();
        $this->parameter->checkArguments();

        $this->assertSame(
            [
                'option1' => 'value1',
            ],
            $this->parameter->getOptions()
        );
        $this->assertSame(
            [
                'arg1' => 'argument1',
                'arg2' => 'argument2 is a string',
                'arg3' => 'argument3!',
            ],
            $this->parameter->getArguments()
        );
    }
}
