<?php
/**
 * @package     Devvoh
 * @subpackage  Components
 * @subpackage  Cli
 * @license     MIT
 * @author      Robin de Graaf <hello@devvoh.com>
 * @copyright   2015 Robin de Graaf, devvoh webdevelopment
 */

namespace Devvoh\Components;

class Cli {
    use \Devvoh\Components\Traits\GetClassName;

    protected $parameters           = array();
    protected $lastProgressLength   = 0;
    protected $lines                = array();

    /**
     * Write a line ending in a line break
     *
     * @param $message
     *
     * @return $this
     */
    public function write($message) {
        echo $message . PHP_EOL;
        return $this;
    }

    /**
     * print_r the $message ending in a line break
     *
     * @param $message
     *
     * @return $this
     */
    public function dump($message) {
        print_r($message);
        echo PHP_EOL;
        return $this;
    }

    /**
     * Add a line to $this->lines array
     *
     * @param $message
     *
     * @return $this
     */
    public function addLine($message) {
        $this->lines[] = $message;
        return $this;
    }

    /**
     * Output all lines from $this->lines
     *
     * @return $this
     */
    public function writeLines() {
        $output = implode($this->lines, PHP_EOL);
        $this->write($output);
        return $this;
    }

    /**
     * Ooutput a new line
     *
     * @return $this
     */
    public function nl() {
        echo PHP_EOL;
        return $this;
    }

    /**
     * Parse $params into array of parameters and values
     *
     * @param $params
     *
     * @return $this
     */
    public function parseParameters($params) {
        // Check for parameters given
        for ($i = 1; $i < count($params); $i++) {
            if (substr($params[$i], 0, 1) === '-') {
                // set the current param as key and the next one as value
                $key = str_replace('-', '', $params[$i]);
                $this->parameters[$key] = $params[$i+1];
                // and skip the value
                $i++;
            } else {
                // Set the parameters as key and true as value
                $this->parameters[$params[$i]] = true;
            }
        }
        return $this;
    }

    /**
     * Return all parameters
     *
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * Get one specific parameter by key or false
     *
     * @param $key
     *
     * @return bool|mixed
     */
    public function getParameter($key) {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }
        return false;
    }

    /**
     * Ask a yes/no question with a $default option and keep asking until a valid answer has been given
     *
     * @param      $question
     * @param bool $default
     *
     * @return bool
     */
    public function yesNo($question, $default = true) {
        // output question and appropriate default value
        echo trim($question) . ($default ? ' [Y/n] ' : ' [y/N] ');
        // get user input from stdin
        $line = fgets(STDIN);
        // turn into lowercase and check specifically for yes and no, call ourselves again if neither
        $value = strtolower(trim($line));

        if (in_array($value, array('y', 'yes'))) {
            return true;
        } elseif (in_array($value, array('n', 'no'))) {
            return false;
        } elseif (empty($value)) {
            // but if it's empty, assume default
            return $default;
        }
        // If nothing has been returned so far, keep asking
        echo "Enter y/yes or n/no.\n";
        return $this->yesNo($question, $default);
    }

    /**
     * Show or update progress message, which will replace itself if called again
     *
     * @param $message
     *
     * @return $this
     */
    public function progress($message) {
        // If lastProgressLength isn't 0, this isn't the first progress call
        if ($this->lastProgressLength > 0) {
            // Go back [x] characters with the following weird string
            echo "\e[" . $this->lastProgressLength . "D";
        }
        // We need to make sure we pad if new string is shorter
        $previousAmount = $this->lastProgressLength;

        // Set lastProgressLength to new message length
        $this->lastProgressLength = strlen($message);

        // And if previousAmount is more than the new amount, pad so we clear all characters
        $newAmount = $this->lastProgressLength;
        if ($previousAmount > $newAmount) {
            $message = str_pad($message, $previousAmount, ' ', STR_PAD_RIGHT);
        }

        echo $message;
        return $this;
    }

    /**
     * Resets the progress last length so progress will not try to go back to the start of the line on next call
     *
     * @return $this
     */
    public function resetProgress() {
        $this->lastProgressLength = 0;
        return $this;
    }

    /**
     * Clean exit of the program
     */
    public function end() {
        $this->writeLines();
        exit;
    }

}