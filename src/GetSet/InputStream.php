<?php

namespace Parable\GetSet;

class InputStream extends \Parable\GetSet\Base
{
    /** @var string */
    protected $resource = 'parable_input_stream';

    /** @var bool */
    protected $useLocalResource = true;

    /** @var string */
    protected $inputSource = 'php://input';

    public function __construct()
    {
        $body_content = file_get_contents($this->inputSource);
        if (!empty($body_content)) {
            $this->extractAndSetData($body_content);
        }
    }

    public function extractAndSetData($data)
    {
        // Attempt to load as Json first, as it's easier to recognise a failure on
        $data_parsed = json_decode($data, true);

        // If there's an error, maybe it's array data? We do this second because parse_str is super-uncaring.
        if (json_last_error() !== JSON_ERROR_NONE) {
            parse_str($data, $data_parsed);
        }

        if (is_array($data_parsed)) {
            $this->setAll($data_parsed);
        }
    }
}
