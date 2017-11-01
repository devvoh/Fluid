<?php

namespace Parable\Http;

class Request
{
    /** @var string */
    protected $protocol;

    /** @var string */
    protected $method;

    /** @var array */
    protected $headers = [];

    /** @var string */
    protected $body;

    /**
     * Set some basic information we're going to need.
     */
    public function __construct()
    {
        if (PHP_SAPI !== "cli") {
            $this->headers = getallheaders() ?: []; // @codeCoverageIgnore
        }
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->getScheme() . "://" . $this->getHttpHost() . "/" . ltrim($this->getRequestUrl(), "/");
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.1";
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->getMethod() === $method;
    }

    /**
     * @return bool
     */
    public function isGet()
    {
        return $this->isMethod('GET');
    }

    /**
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod('POST');
    }

    /**
     * @return bool
     */
    public function isPut()
    {
        return $this->isMethod('PUT');
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return $this->isMethod('DELETE');
    }

    /**
     * @return bool
     */
    public function isPatch()
    {
        return $this->isMethod('PATCH');
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public function getHeader($key)
    {
        foreach ($this->headers as $header => $content) {
            if (strtolower($key) == strtolower($header)) {
                return $content;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * This is surprisingly annoying due to unreliable
     * availability of $_SERVER values.
     *
     * @return string
     */
    public function getScheme()
    {
        if (isset($_SERVER["REQUEST_SCHEME"])) {
            // Apache 2.4+
            return $_SERVER["REQUEST_SCHEME"];
        }
        if (isset($_SERVER["REDIRECT_REQUEST_SCHEME"])) {
            return $_SERVER["REDIRECT_REQUEST_SCHEME"];
        }
        if (isset($_SERVER["HTTP_X_FORWARDED_PROTO"])) {
            // Sometimes available in proxied requests
            return $_SERVER["HTTP_X_FORWARDED_PROTO"];
        }
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") {
            // Old-style but compatible with IIS
            return "https";
        }
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            // Hacky but this is our last attempt, so why not
            return "https";
        }
        return "http";
    }

    /**
     * @return null|string
     */
    public function getHttpHost()
    {
        if (isset($_SERVER["HTTP_HOST"]) && isset($_SERVER["SERVER_NAME"])
            && $_SERVER["HTTP_HOST"] === $_SERVER["SERVER_NAME"]
        ) {
            return $_SERVER["HTTP_HOST"];
        }

        if (isset($_SERVER["HTTP_HOST"])) {
            return $_SERVER["HTTP_HOST"];
        }

        // This is the least reliable, due to the ability to spoof it
        if (isset($_SERVER["SERVER_NAME"])) {
            return $_SERVER["SERVER_NAME"];
        }

        return null;
    }

    /**
     * @return null|string
     */
    public function getRequestUrl()
    {
        return isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : null;
    }

    /**
     * @return null|string
     */
    public function getScriptName()
    {
        return isset($_SERVER["SCRIPT_NAME"]) ? $_SERVER["SCRIPT_NAME"] : null;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        if ($this->body === null) {
            $this->body = file_get_contents('php://input');
        }
        return $this->body;
    }
}
