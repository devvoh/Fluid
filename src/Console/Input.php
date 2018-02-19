<?php

namespace Parable\Console;

class Input
{
    /** @var string[] */
    protected $specialKeys = [
        "esc"         => "%1B",
        "enter"       => "%0A",
        "backspace"   => "%7F",
        "F1"          => "%1BOP",
        "F2"          => "%1BOQ",
        "F3"          => "%1BOR",
        "F4"          => "%1BOS",
        "F5"          => "%1B%5B15%7E",
        "F6"          => "%1B%5B17%7E",
        "F7"          => "%1B%5B18%7E",
        "F8"          => "%1B%5B19%7E",
        "F9"          => "%1B%5B20%7E",
        "F10"         => "%1B%5B21%7E",
        "F11"         => "%1B%5B23%7E%1B",
        "F12"         => "%1B%5B24%7E%08",
        "arrow_left"  => "%1B%5BD",
        "arrow_right" => "%1B%5BC",
        "arrow_down"  => "%1B%5BB",
        "arrow_up"    => "%1B%5BA",
    ];

    /**
     * Request input from the user and require a return at the end.
     *
     * @return string
     */
    public function get()
    {
        return trim(fread(STDIN, 10000));
    }

    /**
     * Return a single key press without waiting for a return. Hide provided input.
     * Will return string values defined in $specialKeys for key presses defined in that array.
     *
     * @return string|null
     */
    public function getKeyPress()
    {
        $this->disableShowInput();
        $this->disableRequireReturn();

        $input = null;
        while (1) {
            $input = fread(STDIN, 10000);
            break;
        }

        $this->enableShowInput();
        $this->enableRequireReturn();

        $specialKey = $this->detectSpecialKey($input);

        return $specialKey ? $specialKey : (string)$input;
    }

    /**
     * Detect whether the key defined in $input is considered a special key.
     *
     * @param string $input
     *
     * @return string|null
     */
    protected function detectSpecialKey($input)
    {
        $specialKey = false;
        if (in_array(ord($input), [27, 127, 10])) {
            $specialKey = array_search(urlencode($input), $this->specialKeys);
        }

        return $specialKey ? $specialKey : null;
    }

    /**
     * Set that we will wait for a user-provided return before returning the input.
     *
     * @return $this
     */
    public function enableRequireReturn()
    {
        if ($this->isInteractiveShell()) {
            system('stty -cbreak');
        }
        return $this;
    }

    /**
     * Set that we will NOT wait for a user-provided return before returning the input.
     *
     * @return $this
     */
    public function disableRequireReturn()
    {
        if ($this->isInteractiveShell()) {
            system('stty cbreak');
        }
        return $this;
    }

    /**
     * Show the input entered by the user to the user.
     *
     * @return $this
     */
    public function enableShowInput()
    {
        if ($this->isInteractiveShell()) {
            system('stty echo');
        }
        return $this;
    }

    /**
     * Do not show the input entered by the user to the user.
     *
     * @return $this
     */
    public function disableShowInput()
    {
        if ($this->isInteractiveShell()) {
            system('stty -echo');
        }
        return $this;
    }

    /**
     * Request input from the user, while hiding the actual input. Use this to request passwords, for example.
     *
     * @return string
     *
     * @throws \Parable\Console\Exception
     */
    public function getHidden()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            throw new \Parable\Console\Exception(
                "Hidden input is not supported on windows."
            );
        }

        $this->disableShowInput();
        $input = $this->get();
        $this->enableShowInput();

        return $input;
    }

    /**
     * Request a y/n input from the user, with a default value highlighted as uppercase ([Y/n], for example).
     *
     * @param bool $default
     *
     * @return bool
     */
    public function getYesNo($default = true)
    {
        $value = strtolower($this->get());

        // Y/N values are ALWAYS directly returned as true/false
        if ($value == 'y') {
            return true;
        } elseif ($value == 'n') {
            return false;
        }

        // If no value, we return the default value
        if ($value == '') {
            return (bool)$default;
        }

        // Anything else should be considered false
        return false;
    }

    /**
     * Return whether we're currently in an interactive shell or not.
     *
     * @return bool
     */
    public function isInteractiveShell()
    {
        return function_exists('posix_isatty') && posix_isatty(0);
    }

    /**
     * Make sure we reset showing input, as it will linger after the script ending if not reset.
     */
    public function __destruct()
    {
        $this->enableShowInput();
    }
}
