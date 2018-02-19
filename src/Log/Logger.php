<?php

namespace Parable\Log;

class Logger
{
    /** @var \Parable\Log\Writer\WriterInterface */
    protected $writer;

    /**
     * Set a writer class to use.
     *
     * @param \Parable\Log\Writer\WriterInterface $writer
     *
     * @return $this
     */
    public function setWriter(\Parable\Log\Writer\WriterInterface $writer)
    {
        $this->writer = $writer;
        return $this;
    }

    /**
     * Write a message to the log writer.
     *
     * @param mixed $message
     *
     * @return $this
     * @throws \Parable\Log\Exception
     */
    public function write($message)
    {
        if (!$this->writer) {
            throw new \Parable\Log\Exception("Can't write without a valid \Log\Writer instance set.");
        }
        $message = $this->stringifyMessage($message);

        $this->writer->write($message);
        return $this;
    }

    /**
     * Write an array of messages to the log writer.
     *
     * @param array $messages
     *
     * @return $this
     * @throws \Parable\Log\Exception
     */
    public function writeLines(array $messages)
    {
        if (!$this->writer) {
            throw new \Parable\Log\Exception("Can't writeLines without a valid \Log\Writer instance set.");
        }
        foreach ($messages as $message) {
            $this->write($message);
        }
        return $this;
    }

    /**
     * Stringify a message so it can be written.
     *
     * @param mixed $message
     *
     * @return string
     */
    protected function stringifyMessage($message)
    {
        if (is_array($message) || is_object($message) || is_bool($message)) {
            return (string)var_export($message, true);
        }
        return (string)$message;
    }
}
