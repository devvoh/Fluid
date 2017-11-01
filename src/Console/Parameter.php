<?php

namespace Parable\Console;

class Parameter
{
    /** Used to designate a parameter as existing but without value */
    const PARAMETER_EXISTS = '__parameter_exists__';

    /** @var array */
    protected $parameters = [];

    /** @var string */
    protected $scriptName;

    /** @var string */
    protected $commandName;

    /** @var array */
    protected $parsedOptions = [];

    /** @var array */
    protected $rawArguments = [];

    /** @var array */
    protected $parsedArguments = [];

    /** @var array */
    protected $commandOptions = [];

    /** @var array */
    protected $commandArguments = [];

    public function __construct()
    {
        $this->setParameters($_SERVER["argv"]);
    }

    /**
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
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return $this
     */
    public function parseParameters()
    {
        // Reset all previously gathered data
        $this->reset();

        // Extract the scriptName
        $this->scriptName = array_shift($this->parameters);

        // Extract the commandName
        if (isset($this->parameters[0])
            && !empty($this->parameters[0])
            && strpos($this->parameters[0], '--') === false
        ) {
            $this->commandName = array_shift($this->parameters);
        }

        $optionName = null;
        foreach ($this->parameters as $key => $parameter) {
            // check if option
            if (substr($parameter, 0, 2) == '--') {
                $parameter = ltrim($parameter, '-');
                // check if there's an '=' sign in there
                $equalsPosition = strpos($parameter, '=');
                if ($equalsPosition !== false) {
                    $optionName  = substr($parameter, 0, $equalsPosition);
                    $optionValue = substr($parameter, $equalsPosition + 1);

                    $this->parsedOptions[$optionName] = $optionValue;
                } else {
                    $this->parsedOptions[$parameter] = self::PARAMETER_EXISTS;
                    $optionName = $parameter;
                }
            } elseif ($optionName) {
                $this->parsedOptions[$optionName] = $parameter;
                $optionName = null;
            } else {
                $this->rawArguments[] = $parameter;
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getScriptName()
    {
        return $this->scriptName;
    }

    /**
     * @return null|string
     */
    public function getCommandName()
    {
        return $this->commandName;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->commandOptions = $options;
        return $this;
    }

    /**
     * @param array $arguments
     *
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->commandArguments = $arguments;
        return $this;
    }

    /**
     * Checks the options set against the parameters set. Takes into account whether an option is required
     * to be passed or not, or a value is required if it's passed, or sets the defaultValue if given and necessary.
     *
     * @throws \Parable\Console\Exception
     */
    public function checkOptions()
    {
        foreach ($this->commandOptions as $option) {
            // Check if required option is actually passed
            if (isset($option['required'])
                && $option['required']
                && !array_key_exists($option['name'], $this->parsedOptions)
            ) {
                throw new \Parable\Console\Exception("Required option '--{$option['name']}' not provided.");
            }

            // Check if non-required but passed option requires a value
            if (array_key_exists($option['name'], $this->parsedOptions)
                && isset($option['valueRequired'])
                && $option['valueRequired']
                && (!$this->parsedOptions[$option['name']]
                    || $this->parsedOptions[$option['name']] == self::PARAMETER_EXISTS
                )
            ) {
                throw new \Parable\Console\Exception(
                    "Option '--{$option['name']}' requires a value, which is not provided."
                );
            }

            // Set default value if defaultValue is set and the option is either passed without value or not passed
            if (isset($option['defaultValue'])
                && $option['defaultValue']
                && (
                    !array_key_exists($option['name'], $this->parsedOptions)
                    || $this->parsedOptions[$option['name']] == self::PARAMETER_EXISTS
                )
            ) {
                $this->parsedOptions[$option['name']] = $option['defaultValue'];
            }
        }
    }

    public function checkArguments()
    {
        foreach ($this->commandArguments as $index => $argument) {
            $key = $index + 1;
            // Check if required argument is actually passed
            if (isset($argument['required'])
                && $argument['required']
                && !array_key_exists($index, $this->rawArguments)
            ) {
                throw new \Parable\Console\Exception("Required argument '{$key}:{$argument['name']}' not provided.");
            }
            if (array_key_exists($index, $this->rawArguments)) {
                $this->parsedArguments[$argument['name']] = $this->rawArguments[$index];
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
        if (!array_key_exists($name, $this->parsedOptions)) {
            return null;
        }
        if ($this->parsedOptions[$name] == static::PARAMETER_EXISTS) {
            return true;
        }
        return $this->parsedOptions[$name];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $returnArray = [];
        foreach ($this->parsedOptions as $key => $option) {
            $returnArray[$key] = $this->getOption($key);
        }
        return $returnArray;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getArgument($name)
    {
        if (!array_key_exists($name, $this->parsedArguments)) {
            $commandArgument = $this->getCommandArgument($name);
            if (!$commandArgument) {
                return null;
            }
            return $commandArgument["defaultValue"];
        }
        return $this->parsedArguments[$name];
    }

    /**
     * @param string $name
     *
     * @return array|null
     */
    public function getCommandArgument($name)
    {
        foreach ($this->commandArguments as $argument) {
            if ($name === $argument["name"]) {
                return $argument;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        $returnArray = [];
        foreach ($this->parsedArguments as $key => $argument) {
            $returnArray[$key] = $this->getArgument($key);
        }
        return $returnArray;
    }

    /**
     * @return $this
     */
    protected function reset()
    {
        $this->scriptName      = null;
        $this->commandName     = null;
        $this->rawArguments    = [];
        $this->parsedArguments = [];
        $this->parsedOptions   = [];

        return $this;
    }
}
