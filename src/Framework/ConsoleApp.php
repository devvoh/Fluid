<?php

namespace Parable\Framework;

class ConsoleApp
{
    /** @var \Parable\Console\App */
    protected $consoleApp;

    /** @var bool */
    protected $errorReportingEnabled = false;

    public function __construct(
        \Parable\Framework\Autoloader $autoloader,
        \Parable\Framework\Config $config,
        \Parable\Console\App $consoleApp,
        \Parable\Filesystem\Path $path,
        \Parable\Console\Command\Help $commandHelp,
        \Parable\Framework\Command\InitStructure $commandInitStructure,
        \Parable\Framework\Package\PackageManager $packageManager
    ) {
        // We're going to want to display all errors.
        $this->setErrorReportingEnabled(true);

        $this->consoleApp = $consoleApp;

        // Add the default location to the autoloader and register it
        $autoloader->addLocation(BASEDIR . DS . 'app');
        $autoloader->register();

        // And make sure $path has the proper BASEDIR
        $path->setBaseDir(BASEDIR);

        // Set the appropriate name
        $this->consoleApp->setName('Parable ' . \Parable\Framework\App::PARABLE_VERSION);

        // Add the init structure command
        $this->consoleApp->addCommand($commandInitStructure);

        // And set help as default
        $this->consoleApp->setDefaultCommand($commandHelp);

        // Attempt to work with the config, if it exists.
        try {
            // Attempt to load additional commands from the config
            $config->load();

            if ($config->get('parable.commands')) {
                $commandLoader = \Parable\DI\Container::get(\Parable\Framework\Loader\CommandLoader::class);
                $commandLoader->load($config->get('parable.commands'));
            }
            // Attempt to load error reporting setting from the config
            if ($config->get('parable.debug') === true) {
                $this->setErrorReportingEnabled(true);
            } else {
                $this->setErrorReportingEnabled(false);
            }
        } catch (\Parable\DI\Exception $exception) { // @codeCoverageIgnore
            // It's fine, we don't need these.
        }

        // And now possible packages get their turn.
        $packageManager->registerPackages();
    }

    /**
     * Enable error reporting, setting display_errors to on and reporting to E_ALL
     *
     * @param bool $enabled
     *
     * @return $this
     */
    public function setErrorReportingEnabled($enabled)
    {
        ini_set('log_errors', 1);

        if ($enabled) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 0);
            error_reporting(E_ALL | ~E_DEPRECATED);
        }

        $this->errorReportingEnabled = $enabled;

        return $this;
    }

    /**
     * Return whether error reporting is currently enabled or not
     *
     * @return bool
     */
    public function isErrorReportingEnabled()
    {
        return $this->errorReportingEnabled;
    }

    /**
     * Run the console application.
     *
     * @return mixed
     * @throws \Parable\Console\Exception
     */
    public function run()
    {
        return $this->consoleApp->run();
    }
}
