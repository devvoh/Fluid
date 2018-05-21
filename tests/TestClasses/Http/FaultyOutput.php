<?php

namespace Parable\Tests\TestClasses\Http;

class FaultyOutput extends \Parable\Http\Output\AbstractOutput
{
    /** @var string */
    protected $contentType = 'application/json';

    /**
     * @inheritdoc
     */
    public function init(\Parable\Http\Response $response)
    {
        $response->setContentType($this->contentType);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function prepare(\Parable\Http\Response $response)
    {
        // We always set the content to be an array, even IF
        return ["this is an array and that's invalid"];
    }
}
