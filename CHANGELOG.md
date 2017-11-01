# Parable PHP Framework Changelog

### 0.12.13

__Changes__
- To ease development a little, try `make server` on a `make`-enabled OS (or run the php built-in webserver yourself with `php-server.php` passed as router script) and it will run (by default) on `http://localhost:5678`.
- `\Parable\Console\Parameter` and `Command` now understand that even arguments deserve default values. Third (optional) parameter added to `addArgument` that will allow for a default value. 
- 5 whitespace issues fixed, based on feedback from StyleCI, using the PSR-2 preset. These were the only(!) style discrepancies found.

__Bugfixes__
- `Undefined offset` fixed in `\Parable\Console\Parameter::checkArguments()`. Requesting arguments that weren't provided no longer results in a notice.

### 0.12.12

__Bugfixes__
- Bug in `\Parable\\Routing\Route::isPartCountSame($url)`, where trailing slash vs no trailing slash would be considered not the same. Fixed in PR #25 by @jerry1970.

### 0.12.11

__Bugfixes__
- I can't believe nobody noticed this yet, but there was a bug in `\Parable\ORM\Query::buildOrderBy()`, where the key is actually escaped through `quote` rather than `quoteIdentifier`, leaving it as a quoted value rather than a key.
- Also `buildGroupBy()`, grmbl.

### 0.12.10

I'm cleaning up this file. All CHANGELOG entries older than the current 'major' pre-release version and the version before that have been moved to CHANGELOG-HISTORY.md.

__Changes__
- `\Parable\GetSet\Base` also got some love. Now, like in `\Parable\Framework\Config`, it's possible to get nested array values with the dot-notation. To get `$resource["one"]["two"]`, you can now use `$config->get("one.two");`. This of course works for all GetSet implementations, so also Post and Get. `$getSet->remove()` also supports this notation now.
- `\Parable\GetSet\Base` can do the same with set now. So to set (and create all currently non-existing) nested keys, you can do this `$config->set("one.two.three", "value");`.
- Since this copies the functionality already built into `\Parable\Framework\Config`, that class has now become an implementation of `\Parable\GetSet\Base`, making it all work exactly the same.

### 0.12.9

__Changes__
- `\Parable\GetSet\BaseInput` has been de-abstracted and renamed. It's now available as `\Parable\GetSet\InputStream`. This deprecates the `Put`, `Patch` and `Delete` GetSetters. This fixes #23.

### 0.12.8

__Bugfixes__
- `\Parable\Http\Url` now has separate base path and script name. So even if you place all but the public files outside of the server's document root, the index.php is still stripped to build the base url. This fixes #22. 

### 0.12.7

__Changes__
- `\Parable\Http\Output\Json` has better error checking in `prepare()`, doing its best to make sure the content ends up as json. If it can't it'll throw an exception. This fixes issue #18.
- `\Parable\Http\Request` has received some love:
  - `getHeader($key)` now also matches on different capitalisation, to make matching easier.
  - `getCurrentUrl()` was added, which builds the current url from `$_SERVER` data.
  - `getHttpHost()` was added, which does its best to return the most reliable value.
  - `getRequestUrl()` was added.
  - `getScriptName()` was added.
  - `getBody()` was added, returning the value of `php://input` passed to the request.
- `ORM\Database` has gained `setCharset()` and `getCharset()`, so it's now possible to specifically set it. This will also be picked up from the config (key `database.charset`). This fixes issue #19. Fixed by @jerry1970.
  
__Bugfixes__
- `\Parable\GetSet\BaseInput` now auto-detects whether data is json or form-data and attempts to load it both ways. PHP doesn't care whether it's `x-www-form-urlencoded` or passed in the body, so it's all about what the data is.
- `\Parable\Model\ORM` was using the literal property `id` to check for a primary key. Now uses `$this->getTableKey()` instead.
- `\Parable\Routing\Route` didn't `rtrim` the / off of the urls. This made matching `url` with `url/` impossible.

### 0.12.6

__Changes__
- `\Parable\Framework\Toolkit::redirectToRoute()` now also accepts a `$parameters` array, to build route Urls with params like `\Parable\Routing\Router::getRouteUrlByName()` does.

__Bugfixes__
- `Init\Example` (structure file) did not yet use or explain the use of `HOOK_x` constants on `\Parable\Framework\App`.

### 0.12.5

__Changes__
- `\Parable\ORM\Model` now has the possibility of `toArrayWithoutEmptyValues` and `exportToArrayWithoutEmptyValues`, and the default `toArray` and `exportToArray` now return empty values.
- `\Parable\ORM\Repository` now offers `buildAndSet()` and `buildOrSet()`. This fixes issue #10.
- All hook `->trigger(...)` calls now make use of constants so that hooking into them is somewhat more comfortable as well. This fixes issue #11.
- It's now possible to add `errorMode` to the database config so you can set the error mode. Use the values defined on `PDO` for this: `ERRMODE_SILENT`, `ERRMODE_WARNING` or `ERRMODE_EXCEPTION`. `ERRMODE_SILENT` is the default, so it's no change for you. Setting more punishing error modes should be a conscious choice. This fixes issue #12.
- Typo in `Init\Example` fixed, was still referencing `initLocations`, whereas that's `inits` now. This fixes #14.
- `\Parable\Http\Request` now no longer sets Method in its `__construct` method, but checks the `$_SERVER` values every time `getMethod` is called.
- `\Parable\Http\Response::setHttpCode()` now throws an Exception when an invalid code is set.

__Bugfixes__
- You can now set a different `homeDir` than `public`, for those who need a different location due to their hosting setup. This makes it possible to keep all app-related code outside of the end-user-accessible dirs. This fixes #13.
- `\Parable\Console` now supports `--option=value` style options, as well as arguments. Simple `$command->addArgument("name", $required = bool)` and you can use them. Arguments are *in-order*, but can be either preceeded or succeeded by options. This fixes #15.
- `\Parable\Http\Response` now returns using the same HTTP protocol it was requested with, since it asks `\Parable\Http\Request` what it should be. This fixes issue #16.  

### 0.12.4

__Bugfixes__
- `\Parable\Framework\Toolkit::redirectToRoute()` did not use `getUrl` to build the url on top of the baseUrl.

### 0.12.2 - 0.12.3

__Bugfixes__
- Left-over but un-DI-ed `$toolkit` property on `\Parable\Framework\View` was preventing use of toolkit in views.
- Removed `composer.lock` since it's useless to end users.

### 0.12.1

__Bugfixes__
- Hook related to dispatching was being triggered twice. Fixed now.

### 0.12.0

__Note: Breaks backwards compatibility. See list below for details how to upgrade from 0.11.x__

__Changes__
- New `Config` array layout. See the example `Config` for the changes!
- `Routing`, `Init` and additional `Config` files now load the same as `Commands` already did. Only `Config\App` is an expected part of Parable, and from there you can just pass in arrays with class names (see new example in structure) and Parable will get the values from them in the order you provide them in. This removes the need for `sortOrder` in Configs, makes `Routing` files easier to split up for readability, and for Inits it really doesn't change much. No more iterating over files! But it does mean you need to add your inits to your Config class and any child Config classes as well.
- `Config` files now need to implement `\Parable\Framework\Interfaces\Config`, no more extends.
- `Routing` files now need to implement `\Parable\Framework\Interfaces\Routing`, no more extends.
- Replaced `strpos` method of deciding whether a method was public or not with `Reflection` logic. Affects `\Parable\ORM\Model::toArray()` and `\Parable\ORM\Model::reset()`.
- No longer add structure folder to the autoload path in `tests/bootstrap.php`, since we no longer use the `_struct` files in testing.
- `\Parable\Framework\App\loadInits()` has been moved up in the run procedure, allowing more triggers: `parable_load_inits_after`, `parable_load_routes_before/after`, `parable_init_database_before/after`, `parable_dispatch_before/after` 

### 0.11.5

__Changes__
- Rename all the files in `structure` to have a `_struct` suffix, so that IDEs don't pick up on then anymore.

### 0.11.4

__Bugfixes__
- `\Parable\DI\Container` had a bug where in some odd instances, while deciding the dependencies to DI for a class, the parameter's class would be `null` and attempting to get the `name` property from `null`, of course, failed.

### 0.11.3

__Changes__
- `\Parable\Http\Response` now only tries to prepend the output buffer if there's data in the buffer and `Response::$content` is already a string.
- `\Parable\GetSet` now has a new type of resource - those that require their data be parsed from `php://input`.
- Three `GetSet` types added due to the above: `\GetSet\Delete`, `\GetSet\Patch` and `\GetSet\Put`. This should make API builders really happy ;)
- `\Parable\ORM\Model` now returns only boolean values on success or fail, instead of a false on fail and a `PDOStatement` on success.
- Changed the command for `\Command\HelloWorld` to `hello-world` because _somebody_ cares too much about that stuff.

### 0.11.2

__Changes__
- Fixed up the README.md file, since it was a bit outdated. Also changed the 'feel' of the text to represent the maturing state of Parable.
- Renamed `init` command to `init-parable`, then immediately renamed it `init-structure` and added a check whether a structure was already initialized, adding a warning if it was.

### 0.11.1

Well, that was fast!

__Bugfixes__
- Fixed a bug in `\Parable\Http\Url`, where it was directly looking at `$_SERVER['REQUEST_SCHEME']`, which isn't always available. Added `\Parable\Http\Request::getScheme` to try out multiple options to figure it out instead.

### 0.11.0

Hey, look! Tests! With 100% code coverage, too! Run `make tests` to run them (which will attempt to `composer install` for needed libraries and then run the tests). Run `make coverage` to run the tests AND generate the HTML coverage report in `./coverage`.

With tests, every nook and cranny of the code was looked at, evaluated and where needed, checked, fixed, removed or added to.

This release also pulls out all interdependencies except for `Framework` still depending on other Components.

There's so many changes that it'll take a fly's lifetime to jot them all down, and it's just not worth it.

If you're upgrading from 0.10, I wish you all the luck, though in most cases the errors will show you the way.

__Changes__
- Many things.

__Bugfixes__
- Much more.

[Check CHANGELOG-HISTORY.md](CHANGELOG-HISTORY.md) for older Changelogs.