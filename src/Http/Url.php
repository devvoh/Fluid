<?php

namespace Parable\Http;

class Url
{
    /** @var \Parable\Http\Request */
    protected $request;

    /** @var string */
    protected $baseUrl;

    /** @var string */
    protected $basePath = "/public";

    /** @var string */
    protected $scriptName = "/index.php";

    public function __construct(
        \Parable\Http\Request $request
    ) {
        $this->request = $request;
    }

    /**
     * @param string $basePath
     *
     * @return $this
     */
    public function setBasePath($basePath)
    {
        if ($basePath) {
            $basePath = "/" . trim($basePath, "/");
        }
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @return string
     */
    public function getScriptName()
    {
        return $this->scriptName;
    }

    /**
     * Initialize the correct baseUrl
     *
     * @return $this
     */
    public function buildBaseUrl()
    {
        $domain = $this->request->getScheme() . '://' . $this->request->getHttpHost();

        $url = $this->request->getScriptName();

        // We only want to remove the first occurrence of our base path, and only if base path is valid
        if ($this->getBasePath()) {
            $basePathPos = strpos($url, $this->getBasePath());
            if ($basePathPos !== false) {
                $url = substr_replace($url, "", $basePathPos, strlen($this->getBasePath()));
            }
        }

        // And we want to remove the script name separately, since it's possible base path is empty
        $url = str_replace($this->getScriptName(), '', $url);

        $this->baseUrl = $domain . '/' . ltrim($url, '/');
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        $this->buildBaseUrl();
        return $this->baseUrl;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function getUrl($url = '')
    {
        return rtrim($this->getBaseUrl(), '/') . '/' . ltrim($url, '/');
    }
}
