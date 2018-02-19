<?php

namespace Parable\Log\Writer;

class File implements \Parable\Log\Writer\WriterInterface
{
    /** @var  */
    protected $logFile;

    /**
     * Write a message to the log file configured.
     *
     * @param string $message
     *
     * @return $this
     * @throws \Parable\Log\Exception
     */
    public function write($message)
    {
        if (!$this->logFile) {
            throw new \Parable\Log\Exception("No log file set. \Log\Writer\File requires a valid target file.");
        }
        $this->writeToFile($message);
        return $this;
    }

    /**
     * Set the log file to write to.
     *
     * @param string $logFile
     *
     * @return $this
     * @throws \Parable\Log\Exception
     */
    public function setLogFile($logFile)
    {
        if (!file_exists($logFile)) {
            // At least attempt to create the file.
            @touch($logFile);
        }
        if (!file_exists($logFile)) {
            throw new \Parable\Log\Exception("Log file is not writable.");
        }

        $this->logFile = $logFile;
        return $this;
    }

    /**
     * Write the message to the log file.
     *
     * @param string $message
     *
     * @return bool|int
     *
     * @codeCoverageIgnore
     */
    protected function writeToFile($message)
    {
        $message = $message . PHP_EOL;
        return @file_put_contents($this->logFile, $message, FILE_APPEND);
    }
}
