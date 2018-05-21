<?php

namespace Parable\Console\Command;

class Help extends \Parable\Console\Command
{
    /** @var string */
    protected $name = 'help';

    /** @var string */
    protected $description = 'Shows all commands available.';

    public function __construct()
    {
        $this->addArgument('command_name');
    }

    /**
     * Show the names and descriptions of all commands set on the application at this moment.
     *
     * @return $this
     */
    public function run()
    {
        if ($this->app->getName()) {
            $this->output->writeln($this->app->getName());
            $this->output->newline();
        }

        $commandName = $this->parameter->getArgument('command_name');
        if ($commandName) {
            $this->showCommandHelp($this->parameter->getArgument('command_name'));
        } else {
            $this->showGeneralHelp();
        }
        return $this;
    }

    /**
     * Show information about all commands.
     */
    protected function showGeneralHelp()
    {
        $this->output->writeln("<yellow>Available commands:</yellow>");

        $longestName = 0;
        foreach ($this->app->getCommands() as $command) {
            $strlen = strlen($command->getName());
            if ($strlen > $longestName) {
                $longestName = $strlen;
            }
        }

        foreach ($this->app->getCommands() as $command) {
            $name = $command->getName();
            $this->output->write(str_pad("  <green>{$name}</green>", $longestName + 22, ' ', STR_PAD_RIGHT));
            $this->output->write("{$command->getDescription()}");
            $this->output->newline();
        }
    }

    /**
     * Show the usage and description for a specific command.
     *
     * @param string $commandName
     */
    protected function showCommandHelp($commandName)
    {
        $command = $this->app->getCommand($commandName);

        if (!$command) {
            $this->output->writeln("<red>Unknown command:</red> {$commandName}");
            return;
        }

        if ($command->getDescription()) {
            $this->output->writeln("<yellow>Description:</yellow>");
            $this->output->writeln("  {$command->getDescription()}");
            $this->output->newline();
        }

        $this->output->writeln("<yellow>Usage:</yellow>");
        $this->output->writeln("  {$command->getUsage()}");
    }
}
