<?php

namespace Parable\Http;

class Request
{
    const METHOD_GET     = "GET";
    const METHOD_POST    = "POST";
    const METHOD_PUT     = "PUT";
    const METHOD_PATCH   = "PATCH";
    const METHOD_DELETE  = "DELETE";
    const METHOD_OPTIONS = "OPTIONS";

    const VALID_METHODS  = [
        self::METHOD_GET,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_PATCH,
        self::METHOD_DELETE,
        self::METHOD_OPTIONS,
    ];

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
     * Return the current full URL.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->getScheme() . "://" . $this->getHttpHost() . "/" . ltrim($this->getRequestUrl(), "/");
    }

    /**
     * Return the protocol from $_SERVER data.
     *
     * @return string
     */
    public function getProtocol()
    {
        return isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.1";
    }

    /**
     * Return the method from $_SERVER data.
     *
     * @return string
     */
    public function getMethod()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
    }

    /**
     * Return the request url.
     *
     * @return null|string
     */
    public function getRequestUrl()
    {
        return isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : null;
    }

    /**
     * Return the script name.
     *
     * @return null|string
     */
    public function getScriptName()
    {
        return isset($_SERVER["SCRIPT_NAME"]) ? $_SERVER["SCRIPT_NAME"] : null;
    }

    /**
     * Return the request body, if any.
     *
     * @return string
     */
    public function getBody()
    {
        if ($this->body === null) {
            $this->body = file_get_contents('php://input');
        }
        return $this->body;
    }

    /**
     * Check whether the current method is $method.
     *
     * @param string $method
     *
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->getMethod() === $method;
    }

    /**
     * Return whether the current request is a GET request.
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->isMethod(self::METHOD_GET);
    }

    /**
     * Return whether the current request is a POST request.
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod(self::METHOD_POST);
    }

    /**
     * Return whether the current request is a PUT request.
     *
     * @return bool
     */
    public function isPut()
    {
        return $this->isMethod(self::METHOD_PUT);
    }

    /**
     * Return whether the current request is a PATCH request.
     *
     * @return bool
     */
    public function isPatch()
    {
        return $this->isMethod(self::METHOD_PATCH);
    }

    /**
     * Return whether the current request is a DELETE request.
     *
     * @return bool
     */
    public function isDelete()
    {
        return $this->isMethod(self::METHOD_DELETE);
    }

    /**
     * Return whether the current request is an OPTIONS request.
     *
     * @return bool
     */
    public function isOptions()
    {
        return $this->isMethod(self::METHOD_OPTIONS);
    }

    /**
     * Return header by key if it exists.
     *
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
     * Return all headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Return the current scheme.
     *
     * This is surprisingly annoying due to unreliable availability of $_SERVER values.
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
            // Sometimes available in proxy-forwarded requests
            return $_SERVER["HTTP_X_FORWARDED_PROTO"];
        }
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") {
            // Old-style but compatible with IIS
            return "https";
        }
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            // This doesn't say much, but this is our last attempt, so why not try
            return "https";
        }
        return "http";
    }

    /**
     * Return the http host, if possible.
     *
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
}
