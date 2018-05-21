<?php

namespace Parable\Console;

class App
{
    /** @var \Parable\Console\Output */
    protected $output;

    /** @var \Parable\Console\Input */
    protected $input;

    /** @var \Parable\Console\Parameter */
    protected $parameter;

    /** @var string|null */
    protected $name;

    /** @var \Parable\Console\Command[] */
    protected $commands = [];

    /** @var \Parable\Console\Command|null */
    protected $activeCommand;

    /** @var string|null */
    protected $defaultCommand;

    /** @var bool */
    protected $onlyUseDefaultCommand = false;

    public function __construct(
        \Parable\Console\Output $output,
        \Parable\Console\Input $input,
        \Parable\Console\Parameter $parameter
    ) {
        $this->output    = $output;
        $this->input     = $input;
        $this->parameter = $parameter;

        set_exception_handler(function ($e) {
            // @codeCoverageIgnoreStart

            /** @var \Exception $e */
            $this->output->writeErrorBlock($e->getMessage());

            if ($this->activeCommand) {
                $this->output->writeln('<yellow>Usage</yellow>: ' . $this->activeCommand->getUsage());
            }
            // @codeCoverageIgnoreEnd
        });
    }

    /**
     * Set the application name..
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Return the application name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add a command to the application.
     *
     * @param \Parable\Console\Command $command
     *
     * @return $this
     */
    public function addCommand(\Parable\Console\Command $command)
    {
        $command->prepare($this, $this->output, $this->input, $this->parameter);
        $this->commands[$command->getName()] = $command;
        return $this;
    }

    /**
     * Add an array of commands to the application.
     *
     * @param \Parable\Console\Command[] $commands
     *
     * @return $this
     */
    public function addCommands(array $commands)
    {
        foreach ($commands as $command) {
            $this->addCommand($command);
        }
        return $this;
    }

    /**
     * Set the default command to use if no command is given (by name).
     *
     * @param string $commandName
     *
     * @return $this
     */
    public function setDefaultCommandByName($commandName)
    {
        $this->defaultCommand = $commandName;
        return $this;
    }

    /**
     * Set the default command to use if no command is given. Also
     * adds the command.
     *
     * @param \Parable\Console\Command $command
     *
     * @return $this
     */
    public function setDefaultCommand(\Parable\Console\Command $command)
    {
        $this->addCommand($command);
        $this->setDefaultCommandByName($command->getName());
        return $this;
    }

    /**
     * Set whether, if a default command is set up, we should consider it the only command.
     *
     * @param bool $onlyUseDefaultCommand
     *
     * @return $this
     */
    public function setOnlyUseDefaultCommand($onlyUseDefaultCommand)
    {
        $this->onlyUseDefaultCommand = (bool)$onlyUseDefaultCommand;
        return $this;
    }

    /**
     * Return whether, if a default command is set up, we should consider it the only command.
     *
     * @return bool
     */
    public function shouldOnlyUseDefaultCommand()
    {
        return $this->onlyUseDefaultCommand;
    }

    /**
     * Returns whether the $commandName is registered.
     *
     * @param string $commandName
     *
     * @return bool
     */
    public function hasCommand($commandName)
    {
        return isset($this->commands[$commandName]);
    }

    /**
     * Return the command by name if it's set on the application.
     *
     * @param string $commandName
     *
     * @return \Parable\Console\Command|null
     */
    public function getCommand($commandName)
    {
        if ($this->hasCommand($commandName)) {
            return $this->commands[$commandName];
        }
        return null;
    }

    /**
     * Return all commands set on the application.
     *
     * @return \Parable\Console\Command[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Remove a command by name.
     *
     * @param string $commandName
     *
     * @return $this
     */
    public function removeCommandByName($commandName)
    {
        if ($this->hasCommand($commandName)) {
            unset($this->commands[$commandName]);
        }
        return $this;
    }

    /**
     * Run the application.
     *
     * @return mixed
     * @throws \Parable\Console\Exception
     */
    public function run()
    {
        $defaultCommand = null;
        $command        = null;

        if ($this->defaultCommand) {
            $defaultCommand = $this->getCommand($this->defaultCommand);
        }
        if (!$this->shouldOnlyUseDefaultCommand()) {
            $commandName = $this->parameter->getCommandName();
            if ($commandName) {
                $command = $this->getCommand($commandName);
            }
            $this->parameter->enableCommandName();
        } else {
            $this->parameter->disableCommandName();
        }

        // Use $command or $defaultCommand, since they're mutually exclusive
        $command = $command ?: $defaultCommand;

        if (!$command) {
            throw new \Parable\Console\Exception('No valid commands found.');
        }

        $this->activeCommand = $command;

        $this->parameter->setCommandArguments($command->getArguments());
        $this->parameter->checkCommandArguments();

        $this->parameter->setCommandOptions($command->getOptions());
        $this->parameter->checkCommandOptions();

        return $command->run();
    }
}
