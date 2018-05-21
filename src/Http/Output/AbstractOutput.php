<?php

namespace Parable\Http\Output;

abstract class AbstractOutput implements OutputInterface
{
    /**
     * @inheritdoc
     */
    public function acceptsContent($content)
    {
        return true;
    }
}
