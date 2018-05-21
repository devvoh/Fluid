<?php

namespace Parable\Console;

class Command
{
    /** @var string|null */
    protected $name;

    /** @var string|null */
    protected $description;

    /** @var callable|null */
    protected $callable;

    /** @var \Parable\Console\Parameter\Option[] */
    protected $options = [];

    /** @var \Parable\Console\Parameter\Argument[] */
    protected $arguments = [];

    /** @var \Parable\Console\App|null */
    protected $app;

    /** @var \Parable\Console\Output|null */
    protected $output;

    /** @var \Parable\Console\Input|null */
    protected $input;

    /** @var \Parable\Console\Parameter|null */
    protected $parameter;

    /**
     * Prepare the command, setting all classes the command is dependant on.
     *
     * @param \Parable\Console\App       $app
     * @param \Parable\Console\Output    $output
     * @param \Parable\Console\Input     $input
     * @param \Parable\Console\Parameter $parameter
     *
     * @return $this
     */
    public function prepare(
        \Parable\Console\App $app,
        \Parable\Console\Output $output,
        \Parable\Console\Input $input,
        \Parable\Console\Parameter $parameter
    ) {
        $this->app       = $app;
        $this->output    = $output;
        $this->input     = $input;
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Set the command name.
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
     * Return the command name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the command description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Return the command description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the callable to be run when the command is run.
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function setCallable(callable $callable)
    {
        $this->callable = $callable;
        return $this;
    }

    /**
     * Return the callable.
     *
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * Add an option for this command.
     *
     * @param string $name
     * @param int    $valueRequired
     * @param mixed  $defaultValue
     *
     * @return $this
     */
    public function addOption(
        $name,
        $valueRequired = Parameter::OPTION_VALUE_OPTIONAL,
        $defaultValue = null
    ) {
        $this->options[$name] = new \Parable\Console\Parameter\Option($name, $valueRequired, $defaultValue);
        return $this;
    }

    /**
     * Return all options for this command.
     *
     * @return \Parable\Console\Parameter\Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Add an argument for this command.
     *
     * @param string $name
     * @param int    $required
     * @param mixed  $defaultValue
     *
     * @return $this
     */
    public function addArgument(
        $name,
        $required = Parameter::PARAMETER_OPTIONAL,
        $defaultValue = null
    ) {
        $this->arguments[] = new \Parable\Console\Parameter\Argument($name, $required, $defaultValue);
        return $this;
    }

    /**
     * Return all arguments for this command.
     *
     * @return \Parable\Console\Parameter\Argument[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Build a usage string out of the arguments and options set on the command.
     * Is automatically called when an exception is caught by App.
     *
     * @return string
     */
    public function getUsage()
    {
        $string = [];

        $string[] = $this->getName();

        foreach ($this->getArguments() as $argument) {
            if ($argument->isRequired()) {
                $string[] = $argument->getName();
            } else {
                $string[] = "[{$argument->getName()}]";
            }
        }

        foreach ($this->getOptions() as $option) {
            if ($option->isValueRequired()) {
                $optionString = "{$option->getName()}=value";
            } else {
                $optionString = "{$option->getName()}[=value]";
            }
            $string[] = "[--{$optionString}]";
        }

        return implode(' ', $string);
    }

    /**
     * Run the callable if it's set. This can be overridden by implementing the run method on a Command class.
     *
     * @return mixed
     */
    public function run()
    {
        $callable = $this->getCallable();
        if (is_callable($callable)) {
            return $callable($this->app, $this->output, $this->input, $this->parameter);
        }
        return false;
    }

    /**
     * Run another command from the current command, passing parameters as an array.
     *
     * @param \Parable\Console\Command $command
     * @param array                    $parameters
     * @return mixed
     */
    protected function runCommand(\Parable\Console\Command $command, array $parameters = [])
    {
        $parameter = new \Parable\Console\Parameter();
        $parameter->setParameters($parameters);

        $command->prepare($this->app, $this->output, $this->input, $parameter);

        return $command->run();
    }
}
