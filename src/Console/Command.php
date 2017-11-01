<?php

namespace Parable\Console;

class Command
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $description;

    /** @var callable */
    protected $callable;

    /** @var array */
    protected $options = [];

    /** @var array */
    protected $arguments = [];

    /** @var \Parable\Console\App */
    protected $app;

    /** @var \Parable\Console\Output */
    protected $output;

    /** @var \Parable\Console\Input */
    protected $input;

    /** @var \Parable\Console\Parameter */
    protected $parameter;

    /**
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
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
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * @param string $name
     * @param bool   $required
     * @param bool   $valueRequired
     * @param mixed  $defaultValue
     *
     * @return $this
     */
    public function addOption($name, $required = false, $valueRequired = false, $defaultValue = null)
    {
        $this->options[$name] = [
            'name'          => $name,
            'required'      => $required,
            'valueRequired' => $valueRequired,
            'defaultValue'  => $defaultValue,
        ];
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @param bool   $required
     * @param mixed  $defaultValue
     *
     * @return $this
     */
    public function addArgument($name, $required = false, $defaultValue = null)
    {
        $this->arguments[] = [
            'name'         => $name,
            'required'     => $required,
            'defaultValue' => $defaultValue,
        ];
        return $this;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
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
