<?php

namespace Parable\Console\Parameter;

class Option extends Base
{
    /** @var int|null */
    protected $optionType;

    public function __construct(
        $name,
        $optionType = \Parable\Console\Parameter::OPTION_VALUE_OPTIONAL,
        $defaultValue = null
    ) {
        $this->setName($name);
        $this->setOptionType($optionType);
        $this->setDefaultValue($defaultValue);
    }

    /**
     * Set whether the option is a flag or has an optional or required value.
     *
     * @param int $optionType
     *
     * @return $this
     * @throws \Parable\Console\Exception
     */
    public function setOptionType($optionType)
    {
        if (!in_array(
            $optionType,
            [
                \Parable\Console\Parameter::OPTION_FLAG,
                \Parable\Console\Parameter::OPTION_VALUE_REQUIRED,
                \Parable\Console\Parameter::OPTION_VALUE_OPTIONAL,
            ]
        )) {
            throw new \Parable\Console\Exception('Value required must be one of the OPTION_* constants.');
        }
        $this->optionType = $optionType;
        return $this;
    }

    public function isFlag()
    {
        return $this->optionType === \Parable\Console\Parameter::OPTION_FLAG;
    }

    /**
     * Return whether the option is not a flag and the option's value is required.
     *
     * @return bool
     */
    public function isValueRequired()
    {
        return $this->optionType === \Parable\Console\Parameter::OPTION_VALUE_REQUIRED;
    }

    /**
     * @inheritdoc
     */
    public function addParameters(array $parameters)
    {
        $this->setProvidedValue(null);
        $this->setHasBeenProvided(false);

        if (!array_key_exists($this->getName(), $parameters)) {
            return $this;
        }

        $this->setHasBeenProvided(true);

        if ($parameters[$this->getName()] !== true) {
            $this->setProvidedValue($parameters[$this->getName()]);
        }

        return $this;
    }
}
