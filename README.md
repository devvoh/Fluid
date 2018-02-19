## Parable PHP Framework

[![Build Status](https://travis-ci.org/devvoh/parable.svg?branch=master)](https://travis-ci.org/devvoh/parable)
[![Latest Stable Version](https://poser.pugx.org/devvoh/parable/v/stable)](https://packagist.org/packages/devvoh/parable)
[![Latest Unstable Version](https://poser.pugx.org/devvoh/parable/v/unstable)](https://packagist.org/packages/devvoh/parable)
[![License](https://poser.pugx.org/devvoh/parable/license)](https://packagist.org/packages/devvoh/parable)
[![StyleCI](https://styleci.io/repos/37279417/shield?branch=master)](https://styleci.io/repos/37279417)

Parable is a PHP micro-framework intended to be readable, extensible and out-of-your-way.

## Installation

Parable can be installed by using [Composer](http://getcomposer.org/).

```bash
$ composer require devvoh/parable
```

This will install Parable and all required dependencies. Parable requires PHP 5.6 or higher.

## Simple Usage

Create an `index.php` file and include the composer autoloader: 

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = \Parable\DI\Container::create(\Parable\Framework\App::class);

$app->get('/hello/{name}', function ($name) use ($app) {
    return "Hello, {$name}!";
});

$app->run();
```

Then go into the `vendor/devvoh/parable` directory and run `make server`. You can then open `http://127.0.0.1:5678/hello/parable` and you should be greeted by "Hello, parable!". You can also serve it through a regular webserver.

## Advanced Usage

To use Parable for more than straightforward apps like in the Basic Usage above, and you want to use Controllers, Actions, Views and more, after installation, run the following command:

```bash
$ vendor/bin/parable init-structure
```

Now you're ready! Simply open the url it should be at in in your browser and you should see a welcome page.

If you want to use nginx or another server, that's cool too, but as of yet there's no example configuration available.

## Getting Started

After you've run `parable init-structure`, you should have a basic structure to work from. The example files show most of what you'll need to build something. The example `\Config\App` file includes some of the most important things Parable itself will listen to.

Now, if you want to keep this out of your git (or other vcs) repository, you can place this in a separate config file and exclude it using a `.gitignore` file.

## More information

Read the [documentation](https://devvoh.com/parable/docs/1.0) for more detailed information on how to use Parable, and [CHANGELOG.md](CHANGELOG.md) for recent changes.

## Contributing

Any help in improving Parable is much appreciated, but check [CONTRIBUTING.md](CONTRIBUTING.md) before creating any pull requests.

## Contact

Any questions or constructive feedback? Find me at [devvoh.com](http://devvoh.com) or ask me a question by adding an issue on github. I generally respond fairly quickly, since this is a passion project, after all.

## License

Parable PHP Framework is open-sourced software licensed under the MIT license.
