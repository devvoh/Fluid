<?php

namespace Parable\Log\Writer;

class NullLogger implements \Parable\Log\Writer\WriterInterface
{
    /**
     * Log it nowhere at all.
     *
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function write($message)
    {
        return $this;
    }
}
