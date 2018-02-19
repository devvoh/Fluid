<?php

namespace Parable\Log\Writer;

interface WriterInterface
{
    /**
     * Write a message to the location the implementation is designed for.
     *
     * @param string $message
     *
     * @return $this
     */
    public function write($message);
}
