<?php
/**
 * @package     Parable Events
 * @license     MIT
 * @author      Robin de Graaf <hello@devvoh.com>
 * @copyright   2015-2016, Robin de Graaf, devvoh webdevelopment
 */

namespace Parable\Command;

class Cli
{
    /**
     * @param string $message
     *
     * @return $this
     */
    public function write($message)
    {
        echo $message;
        return $this;
    }

    /**
     * @return $this
     */
    public function br()
    {
        echo PHP_EOL;
        return $this;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function writeLine($message)
    {
        $this->write($message)->br();
        return $this;
    }

    /**
     * @return string
     */
    public function read()
    {
        $handle = fopen("php://stdin", "r");
        $value = fgets($handle);
        return trim($value);
    }

    /**
     * @return string
     */
    public function readCharacter()
    {
        $handle = fopen("php://stdin", "r");
        $value = fgetc($handle);
        return trim($value);
    }

    /**
     * @return $this
     */
    public function waitForKey()
    {
        passthru('read -rsn1');
        return $this;
    }
}
