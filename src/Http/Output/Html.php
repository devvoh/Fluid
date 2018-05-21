<?php

namespace Parable\Http\Output;

class Html extends \Parable\Http\Output\AbstractOutput
{
    /** @var string */
    protected $contentType = 'text/html';

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
        if (!$this->acceptsContent($response->getContent())) {
            throw new \Parable\Http\Exception('Can only work with string or null content');
        }
        return $response->getContent() ?: null;
    }

    /**
     * This output class only accepts string or null values.
     *
     * @inheritdoc
     */
    public function acceptsContent($content)
    {
        return is_string($content) || $content === null;
    }
}
