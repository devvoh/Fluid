<?php

namespace Parable\Http;

class Response
{
    /** @var int */
    protected $httpCode = 200;

    /** @var int */
    protected $outputBufferLevel = 0;

    /** @var array */
    protected $httpCodes = [
        100 => "Continue",
        101 => "Switching Protocols",

        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",

        300 => "Multiple Choice",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        307 => "Temporary Redirect",
        308 => "Permanent Redirect",

        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Payload Too Large",
        414 => "URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",
        418 => "I'm a teapot",
        421 => "Misdirected Request",
        426 => "Upgrade Required",
        428 => "Precondition Required",
        429 => "Too Many Requests",
        431 => "Request Header Fields Too Large",
        451 => "Unavailable For Legal Reasons",

        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
        506 => "Variant Also Negotiates",
        507 => "Variant Also Negotiates",
        511 => "Network Authentication Required",
    ];

    /** @var string|array */
    protected $content;

    /** @var string */
    protected $contentType;

    /** @var \Parable\Http\Output\OutputInterface */
    protected $output;

    /** @var array */
    protected $headers = [];

    /** @var bool */
    protected $shouldTerminate = true;

    /**
     * By default we're going to set the Html Output type.
     */
    public function __construct(
        \Parable\Http\Request $request
    ) {
        $this->request = $request;

        $this->setOutput(new \Parable\Http\Output\Html);
    }

    /**
     * @param int $httpCode
     *
     * @return $this
     * @throws \Parable\Http\Exception
     */
    public function setHttpCode($httpCode)
    {
        if (!array_key_exists($httpCode, $this->httpCodes)) {
            throw new \Parable\Http\Exception("Invalid HTTP code set: '{$httpCode}'");
        }
        $this->httpCode = $httpCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @return string
     */
    public function getHttpCodeText()
    {
        return $this->httpCodes[$this->httpCode];
    }

    /**
     * @param string $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param \Parable\Http\Output\OutputInterface $output
     *
     * @return $this
     */
    public function setOutput(\Parable\Http\Output\OutputInterface $output)
    {
        $this->output = $output;
        $this->output->init($this);
        return $this;
    }

    /**
     * Send the response
     */
    public function send()
    {
        $buffered_content = $this->returnAllOutputBuffers();
        if (!empty($buffered_content) && is_string($this->content)) {
            $this->content = $buffered_content . $this->content;
        }

        $this->output->prepare($this);

        if (!headers_sent()) {
            // @codeCoverageIgnoreStart
            header("{$this->request->getProtocol()} {$this->getHttpCode()} {$this->getHttpCodeText()}");
            header("Content-type: {$this->getContentType()}");
            foreach ($this->getHeaders() as $key => $value) {
                header("{$key}: {$value}");
            }
            // @codeCoverageIgnoreEnd
        }

        echo $this->getContent();
        $this->terminate();
    }

    /**
     * @param string|array $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string|array
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function appendContent($content)
    {
        if (is_array($this->content)) {
            $this->content[] = $content;
        } else {
            $this->content .= $content;
        }
        return $this;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function prependContent($content)
    {
        if (is_array($this->content)) {
            array_unshift($this->content, $content);
        } else {
            $this->content = $content . $this->content;
        }
        return $this;
    }

    /**
     * Start a new output buffer, upping the internal outputBufferLevel
     *
     * @return $this
     */
    public function startOutputBuffer()
    {
        ob_start();
        $this->outputBufferLevel++;
        return $this;
    }

    /**
     * Return and end the current output buffer if output buffering was started with startOutputBuffer()
     *
     * @return string
     */
    public function returnOutputBuffer()
    {
        if (!$this->isOutputBufferingEnabled()) {
            return "";
        }

        $this->outputBufferLevel--;
        return ob_get_clean();
    }

    /**
     * Return all open output buffering levels started by Parable
     *
     * @return string
     */
    public function returnAllOutputBuffers()
    {
        $content = "";

        if ($this->isOutputBufferingEnabled()) {
            while ($this->isOutputBufferingEnabled()) {
                $content .= $this->returnOutputBuffer();
            }
        }

        return $content;
    }

    /**
     * @return bool
     */
    public function isOutputBufferingEnabled()
    {
        return $this->outputBufferLevel > 0;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public function getHeader($key)
    {
        if (!isset($this->headers[$key])) {
            return null;
        }
        return $this->headers[$key];
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $url
     *
     * @throws \Parable\Http\Exception
     */
    public function redirect($url)
    {
        if (!headers_sent()) {
            header("location: {$url}"); // @codeCoverageIgnore
        }
        $this->terminate();
    }

    /**
     * @param bool $shouldTerminate
     *
     * @return $this
     */
    public function setShouldTerminate($shouldTerminate)
    {
        $this->shouldTerminate = (bool)$shouldTerminate;
        return $this;
    }

    /**
     * @return bool
     */
    public function shouldTerminate()
    {
        return $this->shouldTerminate;
    }

    /**
     * @param int $exitCode
     */
    public function terminate($exitCode = 0)
    {
        if ($this->shouldTerminate()) {
            exit($exitCode); // @codeCoverageIgnore
        }
    }
}
