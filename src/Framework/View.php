<?php

namespace Parable\Framework;

/**
 * The following magic properties are available in template (.phtml) files through $this->...property
 *
 * @property \Parable\Event\Dock                       $dock
 * @property \Parable\Event\Hook                       $hook
 * @property \Parable\Filesystem\Path                  $path
 * @property \Parable\Framework\App                    $app
 * @property \Parable\Framework\Authentication         $authentication
 * @property \Parable\Framework\Config                 $config
 * @property \Parable\Framework\Dispatcher             $dispatcher
 * @property \Parable\Framework\SessionMessage         $sessionMessage
 * @property \Parable\Framework\Toolkit                $toolkit
 * @property \Parable\Framework\View                   $view
 * @property \Parable\Framework\Mail\Mailer            $mailer
 * @property \Parable\Framework\Mail\TemplateVariables $templateVariables
 * @property \Parable\Http\Request                     $request
 * @property \Parable\Http\Response                    $response
 * @property \Parable\Http\Url                         $url
 * @property \Parable\GetSet\Cookie                    $cookie
 * @property \Parable\GetSet\Env                       $env
 * @property \Parable\GetSet\Files                     $files
 * @property \Parable\GetSet\Get                       $get
 * @property \Parable\GetSet\Internal                  $internal
 * @property \Parable\GetSet\Post                      $post
 * @property \Parable\GetSet\Server                    $server
 * @property \Parable\GetSet\Session                   $session
 * @property \Parable\Log\Logger                       $logger
 * @property \Parable\ORM\Query                        $query
 * @property \Parable\ORM\Database                     $database
 * @property \Parable\Routing\Router                   $router
 * @property \Parable\Rights\Rights                    $rights
 */
class View
{
    /** @var \Parable\Filesystem\Path */
    protected $path;

    /** @var \Parable\Http\Response */
    protected $response;

    /** @var string */
    protected $templatePath;

    /** @var array */
    protected $classes = [];

    public function __construct(
        \Parable\Filesystem\Path $path,
        \Parable\Http\Response $response
    ) {
        $this->path     = $path;
        $this->response = $response;

        $this->registerClassesFromMagicProperties();
    }

    /**
     * For all the magic properties defined at the start of this class, loop through them
     * and add them to our list of magic properties.
     *
     * @return $this
     */
    protected function registerClassesFromMagicProperties()
    {
        $reflection      = new \ReflectionClass(self::class);

        $docComment      = $reflection->getDocComment();
        $magicProperties = $docComment ? explode(PHP_EOL, $docComment) : [];

        foreach ($magicProperties as $magicProperty) {
            if (strpos($magicProperty, "@property") === false) {
                continue;
            }

            $partsString = trim(str_replace("* @property", "", $magicProperty));
            $parts       = explode('$', $partsString);

            list($className, $property) = $parts;

            $this->registerClass(trim($property), trim($className));
        }
        return $this;
    }

    /**
     * Register a class with the View for ->property lazyloading.
     *
     * @param string $property
     * @param string $className
     */
    public function registerClass($property, $className)
    {
        // Make sure the $className is prefixed with a backslash
        $className = "\\" . ltrim($className, "\\");

        $this->classes[$property] = $className;
        return $this;
    }

    /**
     * Set the template path used for this view.
     *
     * @param string $templatePath
     *
     * @return $this
     */
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;
        return $this;
    }

    /**
     * Load a template path, interpret it fully and then return the resulting output as a string.
     *
     * @param string $templatePath
     *
     * @return string
     */
    public function partial($templatePath)
    {
        $this->response->startOutputBuffer();
        $this->loadTemplatePath($templatePath);
        return $this->response->returnOutputBuffer();
    }

    /**
     * Render the template from the configured templatePath.
     *
     * @return $this
     */
    public function render()
    {
        $this->loadTemplatePath($this->templatePath);
        return $this;
    }

    /**
     * Attempt to load the templatePath.
     *
     * @param string $templatePath
     *
     * @return $this
     */
    protected function loadTemplatePath($templatePath)
    {
        $templatePath = $this->path->getDir($templatePath);
        if (file_exists($templatePath)) {
            require($templatePath);
        }
        return $this;
    }

    /**
     * Magic get function to pass through all calls to DI-able classes that have been registered with the View.
     *
     * @param string $property
     *
     * @return object
     * @throws \Parable\Framework\Exception
     */
    public function __get($property)
    {
        if (!isset($this->classes[$property])) {
            throw new \Parable\Framework\Exception(
                "Could not find property '{$property}'. Make sure it was registered with the View."
            );
        }
        return \Parable\DI\Container::get($this->classes[$property]);
    }
}
