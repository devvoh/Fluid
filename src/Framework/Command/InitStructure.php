<?php

namespace Parable\Framework\Command;

class InitStructure extends \Parable\Console\Command
{
    /** @var string */
    protected $name = 'init-structure';

    /** @var string */
    protected $description = 'This command initializes a default parable structure.';

    /** @var \Parable\Filesystem\Path */
    protected $path;

    /** @var string */
    protected $vendor_path;

    public function __construct(
        \Parable\Filesystem\Path $path
    ) {
        $this->addOption(
            "homedir",
            \Parable\Console\Parameter::OPTION_VALUE_REQUIRED,
            "public"
        );

        $this->path = $path;
        $this->path->setBaseDir(BASEDIR);

        // Since we can't rely on the package name 100%, we set the vendor_path backwards from here.
        $this->vendor_path = realpath(__DIR__ . DS . ".." . DS . ".." . DS . "..");
    }

    /**
     * Run the init structure command.
     *
     * @return $this
     */
    public function run()
    {
        $homedir = $this->parameter->getOption("homedir");
        $homedir = ltrim($homedir, DS);

        $homedir_actual = $this->path->getDir($homedir);

        $this->output->writeln([
            "Parable initialization script",
            "-----------------------------------",
            "This script will initialize Parable's structure.",
            "",
            "The full 'home directory' will be '<green>{$homedir_actual}</green>',",
            "but you can use the --homedir option to change this.",
            "",
            "Example: <yellow>parable init-structure --homedir=http_docs</yellow>",
            "",
            "<red>WARNING</red>",
            "This will overwrite existing files without notice!",
        ]);

        if (file_exists($this->path->getDir('app')) && file_exists($this->path->getDir('public'))) {
            $this->output->writeBlock("Note: It looks like you already have a structure initialized!", "info");
        } else {
            $this->output->newline();
        }

        for (;;) {
            $this->output->write("Do you want to continue? [y/N] ");
            if ($this->input->getYesNo(false)) {
                break;
            } else {
                $this->output->writeln(["", "<red>You chose not to continue.</red>", ""]);
                return $this;
            }
        }

        $this->output->newline();
        $this->output->write('Creating folder structure: ');

        $dirs = [
            "app",
            "app/Command",
            "app/Config",
            "app/Controller",
            "app/Init",
            "app/Model",
            "app/Routing",
            "app/View",
            "app/View/Home",
            $homedir,
        ];

        foreach ($dirs as $dir) {
            if (!file_exists($this->path->getDir($dir))) {
                mkdir($this->path->getDir($dir));
            }
            $this->output->write('.');
        }

        $this->output->writeln(" <green>OK</green>");

        $this->output->write('Copying files: ');
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/.htaccess"),
            $this->path->getDir(".htaccess")
        );
        $this->output->write('.');
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/dynamicReturnTypeMeta.json"),
            $this->path->getDir("dynamicReturnTypeMeta.json")
        );
        $this->output->write('.');
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/public/index.php_struct"),
            $this->path->getDir("{$homedir}/index.php")
        );
        $this->output->write('.');
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/public/.htaccess"),
            $this->path->getDir("{$homedir}/.htaccess")
        );
        $this->output->write('.');

        // And we continue copying files
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/app/Command/HelloWorld.php_struct"),
            $this->path->getDir("app/Command/HelloWorld.php")
        );
        $this->output->write('.');
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/app/Config/App.php_struct"),
            $this->path->getDir("app/Config/App.php")
        );
        $this->output->write('.');
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/app/Config/Custom.php_struct"),
            $this->path->getDir("app/Config/Custom.php")
        );
        $this->output->write('.');
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/app/Controller/Home.php_struct"),
            $this->path->getDir("app/Controller/Home.php")
        );
        $this->output->write('.');
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/app/Init/Example.php_struct"),
            $this->path->getDir("app/Init/Example.php")
        );
        $this->output->write('.');
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/app/Model/User.php_struct"),
            $this->path->getDir("app/Model/User.php")
        );
        $this->output->write('.');
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/app/Routing/App.php_struct"),
            $this->path->getDir("app/Routing/App.php")
        );
        $this->output->write('.');
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/app/View/Home/index.phtml_struct"),
            $this->path->getDir("app/View/Home/index.phtml")
        );
        $this->output->write('.');
        copy(
            $this->path->getDir("{$this->vendor_path}/structure/app/View/Home/test.phtml_struct"),
            $this->path->getDir("app/View/Home/test.phtml")
        );
        $this->output->write('.');

        // If the homedir isn't 'public', change the values in Config\App.php and .htaccess.
        if ($homedir !== "public") {
            $config = file_get_contents($this->path->getDir("app/Config/App.php"));
            $config = str_replace('"homedir" => "public"', '"homedir" => "' . $homedir . '"', $config);
            file_put_contents($this->path->getDir("app/Config/App.php"), $config);
            $this->output->write('.');

            $htaccess = file_get_contents($this->path->getDir('.htaccess'));
            $htaccess = str_replace("public/$1", "{$homedir}/$1", $htaccess);
            file_put_contents($this->path->getDir('.htaccess'), $htaccess);
        }
        $this->output->write('.');

        $this->output->writeln(" <green>OK</green>");

        $this->output->writeln(["", "<green>Completed!</green>", ""]);
        return $this;
    }
}
