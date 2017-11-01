## Parable PHP Framework

[![Build Status](https://travis-ci.org/devvoh/parable.svg?branch=master)](https://travis-ci.org/devvoh/parable)
[![Latest Stable Version](https://poser.pugx.org/devvoh/parable/v/stable)](https://packagist.org/packages/devvoh/parable)
[![Latest Unstable Version](https://poser.pugx.org/devvoh/parable/v/unstable)](https://packagist.org/packages/devvoh/parable)
[![License](https://poser.pugx.org/devvoh/parable/license)](https://packagist.org/packages/devvoh/parable)
[![StyleCI](https://styleci.io/repos/37279417/shield?branch=master)](https://styleci.io/repos/37279417)

Parable is a small and no-nonsense PHP framework, meant to be fast, readable and written in a way where it's not bogging 
you down with unnecessary rules and limitations. Developed with the goal of building small web applications and REST APIs.

Parable has been in pre-release for a long time (and for many versions) but a 1.0.0 release, which will also bring
documentation, is a goal for 2017.

## Requirements

- PHP 5.6, PHP 7.x
- Composer

## Installation

Parable can be installed by using [Composer](http://getcomposer.org/). Simply run:

`composer require devvoh/parable`

After you've run the above command, you'll have a composer.json, composer.lock and a vendor folder. Parable is in there, 
but it's not quite ready to be used. To initialize Parable's folder structure and files, run the following command:

`vendor/bin/parable init-structure`

Now you're ready! Simply open the (properly installed apache2 & php 5.6 or 7.x) url it's in in your browser and you should
see a welcome page.

If you want to use nginx or another browser, that's cool too, but you'll have to set up url rewriting rules yourself.

## Getting Started

After you've run `parable init-structure`, you should have a basic structure to work from. The example files show most 
of what you'll need to build something. The example `\Config\App` file includes some of the most important things 
Parable itself will listen to.

Now, if you want to keep this out of your git (or other vcs) repository, you can place this in a separate config
file and exclude it using a `.gitignore` file. Parable will attempt to load any Config files located in `app/Config`.

## Documentation & More

Since the API of Parable was in heavy flux, no documentation has been attempted yet. With the release of 0.11.0, the
API is pretty much set for now, and the documentation writing process will start. 

## Contact

Any questions or constructive feedback? Find me at [devvoh.com](http://devvoh.com) or ask me a question by adding an 
issue on github. I generally respond fairly quickly, since this is a passion project, after all.

## License

Parable PHP Framework is open-sourced software licensed under the MIT license.
