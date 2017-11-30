<?php

namespace Parable\Console;

class Parameter
{
    const PARAMETER_REQUIRED    = 1;
    const PARAMETER_OPTIONAL    = 2;

    const OPTION_VALUE_REQUIRED = 11;
    const OPTION_VALUE_OPTIONAL = 12;

    /** @var array */
    protected $parameters = [];

    /** @var string|null */
    protected $scriptName;

    /** @var string|null */
    protected $commandName;

    /** @var array */
    protected $options = [];

    /** @var array */
    protected $arguments = [];

    /** @var \Parable\Console\Parameter\Option[] */
    protected $commandOptions = [];

    /** @var \Parable\Console\Parameter\Argument[] */
    protected $commandArguments = [];

    /** @var bool */
    protected $commandNameEnabled = true;

    public function __construct()
    {
        $this->setParameters($_SERVER["argv"]);
    }

    /**
     * Set parameters and parse them.
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
        $this->parseParameters();
        return $this;
    }

    /**
     * Return the currently set parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Split the parameters into script name, command name, options and arguments.
     *
     * @return $this
     */
    public function parseParameters()
    {
        $this->reset();

        // Extract the scriptName
        $this->scriptName = array_shift($this->parameters);

        foreach ($this->parameters as $parameter) {
            if (substr($parameter, 0, 2) === "--") {
                // For options, we need to see if it has a value (x=y) or not.
                $optionParts = explode("=", $parameter);

                if (count($optionParts) > 1) {
                    list($key, $value) = $optionParts;
                    $this->options[ltrim($key, "-")] = $value;
                } else {
                    $this->options[ltrim($parameter, "-")] = true;
                }
            } else {
                // For arguments, we need to see if the first one is the command name or not.
                if ($this->commandNameEnabled && !$this->commandName) {
                    $this->commandName = $parameter;
                } else {
                    $this->arguments[] = $parameter;
                }
            }
        }

        return $this;
    }

    /**
     * Return the script name.
     *
     * @return string
     */
    public function getScriptName()
    {
        return $this->scriptName;
    }

    /**
     * Return the command name.
     *
     * @return null|string
     */
    public function getCommandName()
    {
        return $this->commandName;
    }

    /**
     * Set the options from a command.
     *
     * @param \Parable\Console\Parameter\Option[] $options
     *
     * @return $this
     */
    public function setCommandOptions(array $options)
    {
        foreach ($options as $name => $option) {
            if ((!$option instanceof Parameter\Option)) {
                throw new \Parable\Console\Exception(
                    "Options must be instances of Parameter\\Option. {$name} is not."
                );
            }
            $this->commandOptions[$option->getName()] = $option;
        }
        return $this;
    }

    /**
     * Checks the options set against the parameters set. Takes into account whether an option is required
     * to be passed or not, or a value is required if it's passed, or sets the defaultValue if given and necessary.
     *
     * @throws \Parable\Console\Exception
     */
    public function checkCommandOptions()
    {
        foreach ($this->commandOptions as $option) {
            $option->addParameters($this->options);

            if ($option->isRequired() && !$option->hasBeenProvided()) {
                throw new \Parable\Console\Exception(
                    "Required option '--{$option->getName()}' not provided."
                );
            }

            if ($option->isValueRequired() && $option->hasBeenProvided() && !$option->getValue()) {
                throw new \Parable\Console\Exception(
                    "Option '--{$option->getName()}' requires a value, which is not provided."
                );
            }
        }
    }

    /**
     * Returns null if the value doesn't exist. Otherwise, it's whatever was passed to it or set
     * as a default value.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->commandOptions)) {
            return null;
        }

        $option = $this->commandOptions[$name];

        if ($option->hasBeenProvided() && !$option->getProvidedValue() && !$option->getDefaultValue()) {
            return true;
        }

        return $option->getValue();
    }

    /**
     * Return all option values.
     *
     * @return array
     */
    public function getOptions()
    {
        $returnArray = [];
        foreach ($this->commandOptions as $option) {
            $returnArray[$option->getName()] = $this->getOption($option->getName());
        }
        return $returnArray;
    }

    /**
     * Set the arguments from a command.
     *
     * @param \Parable\Console\Parameter\Argument[] $arguments
     *
     * @return $this
     */
    public function setCommandArguments(array $arguments)
    {
        $orderedArguments = [];
        foreach ($arguments as $index => $argument) {
            if (!($argument instanceof Parameter\Argument)) {
                throw new \Parable\Console\Exception(
                    "Arguments must be instances of Parameter\\Argument. The item at index {$index} is not."
                );
            }

            $argument->setOrder($index);
            $orderedArguments[$index] = $argument;
        }
        $this->commandArguments = $orderedArguments;

        return $this;
    }

    /**
     * Checks the arguments set against the parameters set. Takes into account whether an argument is required
     * to be passed or not.
     *
     * @throws \Parable\Console\Exception
     */
    public function checkCommandArguments()
    {
        foreach ($this->commandArguments as $index => $argument) {
            $argument->addParameters($this->arguments);

            if ($argument->isRequired() && !$argument->hasBeenProvided()) {
                throw new \Parable\Console\Exception(
                    "Required argument with index #{$index} '{$argument->getName()}' not provided."
                );
            }
        }
    }

    /**
     * Returns null if the value doesn't exist. Returns default value if set from command, and the actual value
     * if passed on the command line.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function getArgument($name)
    {
        foreach ($this->commandArguments as $argument) {
            if ($argument->getName() === $name) {
                return $argument->getValue();
            }
        }
        return null;
    }

    /**
     * Return all arguments passed.
     *
     * @return array
     */
    public function getArguments()
    {
        $returnArray = [];
        foreach ($this->commandArguments as $argument) {
            $returnArray[$argument->getName()] = $this->getArgument($argument->getName());
        }
        return $returnArray;
    }

    /**
     * Reset the class to a fresh state.
     *
     * @return $this
     */
    protected function reset()
    {
        $this->scriptName      = null;
        $this->commandName     = null;
        $this->options         = [];
        $this->arguments       = [];

        return $this;
    }

    /**
     * Remove the command name from the arguments, if a command name is actually set.
     *
     * @return $this;
     */
    public function enableCommandName()
    {
        $this->commandNameEnabled = true;
        if ($this->commandName) {
            $this->arguments = array_diff($this->arguments, [$this->commandName]);
            $this->arguments = array_values($this->arguments);
        }
        return $this;
    }

    /**
     * Add the command name to the arguments, if a command name is set.
     *
     * @return $this;
     */
    public function disableCommandName()
    {
        $this->commandNameEnabled = false;
        if ($this->commandName) {
            array_unshift($this->arguments, $this->commandName);
        }
        return $this;
    }
}
