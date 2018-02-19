<?php

namespace Parable\Http\Output;

interface OutputInterface
{
    /**
     * When the output is initialized, it's possible to set certain values to set up the proper output values.
     *
     * @param \Parable\Http\Response $response
     *
     * @return $this
     */
    public function init(\Parable\Http\Response $response);

    /**
     * Prepare the content for output according to the output type.
     *
     * @param \Parable\Http\Response $response
     *
     * @return $this
     */
    public function prepare(\Parable\Http\Response $response);
}
