<?php

namespace Parable\Http\Output;

class Json implements \Parable\Http\Output\OutputInterface
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
        $content = $response->getContent();

        // Encode if it isn't already a valid json string
        if (!$this->isJsonString($content)) {
            $content = json_encode($content);
        }

        // Final check, if it's still not a valid json string, we give up.
        if (!$this->isJsonString($content)) {
            throw new \Parable\Http\Exception("Json encode error: '" . json_last_error_msg() . "'"); // @codeCoverageIgnore
        }

        $response->setContent($content);
        return $this;
    }

    /**
     * Attempt to check whether the provided string is json or not.
     *
     * @param string $data
     *
     * @return bool
     */
    protected function isJsonString($data)
    {
        if (!is_string($data) && !is_null($data)) {
            return false;
        }

        // We json_encode here to see if there might be a json_last_error
        json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        return true;
    }
}
