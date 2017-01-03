<?php

namespace Parable\Http\Output;

class Html implements \Parable\Http\Output\OutputInterface
{
    /** @var string */
    protected $contentType = 'text/html';

    /**
     * @inheritdoc
     */
    public function init(\Parable\Http\Response $response)
    {
        $response->setContentType($this->contentType);
    }

    /**
     * @inheritdoc
     */
    public function prepare(\Parable\Http\Response $response)
    {
    }
}
