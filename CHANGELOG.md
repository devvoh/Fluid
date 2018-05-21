# Parable PHP Framework Changelog

### 1.1.0

__Changes__

- The `parable` command has been fixed up massively. There's now a `\Parable\Framework\ConsoleApp` class, which handles the actual logic. By using this, you can offer your own command instead of `parable`.
- `defines.php` now sets a global constant `APP_CONTEXT` to either `web` or `cli`. This can help you figure out what context you're running in.
- `defines.php` now also defines a new function: `register_parable_package()`. This can be used by external Parable Packages to register themselves with Parable at the soonest possible moment. See below for details.
- `\Parable\Console\App` now also adds the command you set through `setDefaultCommand()`. It's now also possible to `removeCommandByName()`.
- `\Parable\DI\Container` gained `getDependenciesFor()` so it's possible to get just an array of instantiated dependencies.
- `\Parable\Framework\App` gained multiple hooks: `HOOK_LOAD_CONFIG_BEFORE`/`AFTER`, `HOOK_LOAD_INITS_BEFORE`/`AFTER`, `HOOK_LOAD_LAYOUT_BEFORE`/`AFTER`.
- `\Parable\Framework\App` now supports layouts, which are loaded just before the response is sent. See the `Response` changes below for details. The config values used to load the templates are `parable.layout.header` and `parable.layout.footer`. They're expected to be `.phtml` files.
- `\Parable\Framework\Loader` has been added, containing `InitLoader` and `CommandLoader`, easing the use of either and separating that logic away nicely.
- `\Parable\Framework\Package\PackageManager` was added, allowing external packages to register themselves with Parable before Parable is actually completely set up. Packages are loaded _before_ config and anything else. They get to use the hooks mentioned above.
- `\Parable\Framework\Package\PackageInterface` is an interface used to define a Parable Package.
- `\Parable\Framework\Authentication` has gained `resetUser()` and `reset()`, which calls it and `revokeAuthentication()` both. This makes it possible to unset the user as well.
- `\Parable\Framework\Authentication` has also gained `setUserIdProperty()` and `getUserIdProperty()`, for more control over how to load users.
- `\Parable\Http\Output\OutputInterface` has gained `acceptsContent($content)`, which will return a boolean for whether it accepts a type of content or not.
- `\Parable\Http\Output\OutputInterface::prepare()` now has to return a string value. Always. Exception thrown if not. This makes Output behavior expected.D 
- `\Parable\Http\Output\AbstractOutput` was added, which implements `acceptsContent()` to return default `true`.
- `\Parable\Http\Output\Html` overrides `acceptsContent()` to only accept `string` or `null` types. `Json` accepts all types.
- `\Parable\Http\Response` has gained `setHeaderContent()` and `setFooterContent`, which are pre/appended to the content when sending. This is used by `App`'s layout logic. Also there's getters for both.
- `\Parable\Http\Response` also gained `stopOutputBuffer()`, which does the same as `returnOutputBuffer()` but doesn't return anything. `stopAllOutputBuffers()` pretty much does what it says.
- `\Parable\ORM\Query`'s join methods now all accept a new optional parameter, `$tableName`. Normally, the table name is set to the table already set on the query. But now you can override it. This makes it possible to join tables with other tables, neither of which are forced to be the main table.
- `\Parable\ORM\Query` has gained `whereCondition()`, taking the standard `$key`, `$comparator` and optional `$value` (default `null`)  and `$tableName`. This was added to ease adding simple wheres, without having to _always_ build a condition set.
- `\Parable\ORM\Query\ConditionSet` now accepts a 4th parameter, which is `$tableName`, in case you want to check against a different table's values.
- `\Parable\Rights\Rights` has gained `getRightsNames()`, which will return the names of all rights configured. 

__Bugfixes__

- `\Parable\Console\Output` has had its tags fixed up. It's now possible to combine fore- and background colors, as was always intended. Some small typo fixes in the tag names, but they're easy to fix.
- `\Parable\Framework\App` has lost some classes from its constructor. They're now loaded on an as-needed basis. So if you don't need the session, it won't be loaded, for example.
- `\Parable\Framework\App` now loads the database immediately after loading the Config, instead of much later.
- `\Parable\Framework\Dispatcher` didn't check route return values and blindly attempted to string-concatenate them.  With the help of the `Output` changes, it now checks what kind of data it is and handles it accordingly.
- `\Parable\GetSet\Base` now throws an exception when `getAll()` is called for a global resource type, but the resource doesn't exist. Example case: attempting to use session data before the session is started.
- `\Parable\Http\Response` now checks whether it's possible to append output buffers to content, and uses `acceptsContent` to make sure only valid content is set using the available output method.
- `\Parable\ORM\Model` now returns all fields when `exportToArray()` is called and no `$model->exportable` values are actually available. Remember, Parable's not here to hold your hand. You're responsible for only exporting the right data!

__Parable Packages Information__

Parable Packages are rather simple. Say you want to build something that relies on and extends Parable. If so, just create a class that implements `PackageInterface`, implement the methods defined there, and in your composer.json, make sure that under `autoload`/`files` it loads a php file (_after_ `defines.php` itself is loaded) that calls `register_parable_package()` (as defined in Parable's own `defines.php`) and passes the name of your package file.

Parable will attempt to load the commands and inits defined in your parable package file, and they'll be available from the start of the application's runtime. The commands _will_ be available from the default `parable` command as well.

More details will be added to the documentation once released.

### 1.0.0

Considering the 0.11.x branch as Release Candidate 1 and the 0.12.x branch as RC2, it's time to ship final Parable. This release brings a major clean-up, documentation, and some useful additions.

If you're new to Parable, welcome! None of this is relevant for you :)

__Changes__

- All method doc blocks now have explanatory text, even if it's superfluous, for documentation purposes.
- `\Parable\Console\App` now supports adding multiple commands in one go, using `addCommands([...])`.
- `\Parable\Console\Command\Help` now can generate a string for the usage of a command. Try it yourself: `vendor/bin/parable help init-structure`. Usage is also added to any exception caught by `\Parable\Console\App`'s exception handler.
- `\Parable\Console\Input` received the following updates:
  - `\Parable\Console\Input::getKeyPress()` has been added. It will wait for a single key press and return its value immediately. Special characters like arrow keys, escape, enter, etc, will be returned as a string value accordingly.
  - `\Parable\Console\Input::enableShowInput()` and its buddy `disable` are now available for you to use. If disabled, hides the user's input as they enter it. `Input` will call the `enable` on destruct to prevent its effects lingering after exiting the script.
  - `\Parable\Console\Input::enableRequireReturn()` and its buddy `disable` are now available for you to use as well. If disabled, no longer requires an enter before returning input.
- `\Parable\Console\Output` received the following updates:
  - `\Parable\Console\Output::writeBlockWithTags()` was added, making it possible to write a block with multiple tags.
  - `\Parable\Console\Output::getTerminalWidth()` will return the columns available in the current terminal window. `getTerminalHeight()` will return the lines available.
  - `\parable\Console\Output::isInteractiveShell()` will return whether the script is running in an interactive terminal session or not.
- `\Parable\Console\Parameter` has been rewritten, and options and arguments are no longer just arrays of data, but actual classes. This allows much more fine-grained control over whether, for example, an option has been provided but there's no value to go with it.
- `\Parable\Framework\App` received the following updates:
    - It now has a `HOOK_LOAD_ROUTES_NO_ROUTES_FOUND` constant and triggers it when, you guessed it, no routes are found.
    - Quickroutes! It now has `get()`, `post()`, `put()`, `patch()`, `delete()`, `options()`, `any()` and `multiple()` methods, so there's an easy way of defining callback routes without having to set up the entire structure. `any()` accepts literally any method, or you can pass an array of the methods to `multiple()` as its first parameter.
    - Quickroutes also accept `["controller", "action"]` style callbacks and will set them as discrete `controller` and `action` values on a route if the action is not a static function, to keep load down.
    - It also has `setErrorReportingEnabled($bool)` and `isErrorReportingEnabled()`. By default it's set to off. You can add `parable.debug` to the Config and set it to true to enable it.
    - It can now set the default timezone if you add a `parable.timezone` value to the config. 
- `\Parable\Framework\Dispatcher` received the following updates:
  - It can now return the route it dispatched by calling `getDispatchedRoute()`.
  - It now triggers two more events: `HOOK_DISPATCH_TEMPLATE_BEFORE` and `HOOK_DISPATCH_TEMPLATE_AFTER`. Use this to do something between a controller/callable being called and the template being loaded.
- `\Parable\Framework\Mailer` now supports setting a different mail sender. Default is, as it was, php's own `mail()`.
- `\Parable\Framework\Mailer` now can act on three config values:
  - `parable.mail.sender`, which should be the class name of the `SenderInterface` implementation you want to use.
  - `parable.mail.from.email`, the email for the for address.
  - `parable.mail.from.name`, the name for the for address.
- `\Parable\Framework\View` now accepts more classes to be registered for use within Views. Call `$view->registerClass($property, $className)` and you can do `$this->property_name->stuff()` in your views.
- `\Parable\GetSet\Base` now also has `setResource`, for when you want to switch, or set it using a method rather than overwriting a property.
- `\Parable\GetSet\Base::get()` now accepts a second parameter `$default` which is the value to return when the requested `$key` is not found. Added by @dmvdbrugge in PR #30. Thanks!
- `\Parable\Http\Request` now has `isOptions()` to check for OPTIONS method requests.
- `\Parable\Http\Request` now has constants for all methods and all accepted methods are in `Request::VALID_METHODS`.
- `\Parable\Http\Response` now has `setHeaders()` so you can add a bunch of headers in one call, `removeHeader($key)` so you can remove a header, and `clearHeaders()` to, y'know, actually, I think you got this.
- `\Parable\Http\Response::clearContent()` was added, in case you want to just want to call `appendContent()` multiple times rather than one `setContent()` and then those appends.
- `\Parable\Log\Writer\NullLogger` was added, for when you want to log nowhere at all.
- `\Parable\Mail\Mailer` now obviously also supports setting a Mail Sender. Default is, well, none. That's all up to you to configure. (Hey, psst, `Framework\Mailer` already tries to do that for you!)
- `\Parable\ORM\Model::create()` has been added, making it much easier to instantiate a model. `$user = \Model\User::create()` is now possible.
- `\Parable\ORM\Repository::createForModel()` has been added, and will return a Repository instance for an already instantiated model.
- `\Parable\ORM\Repository::getByCondition()` has lost its 4th parameter `$andOr`, as it would always be a single condition and neither AND nor OR would ever come into play.
- `\Parable\ORM\Repository::reset()` has been added, and will reset Order by, Limit & Offset, only count and return one/all to their default values.
- `\Parable\ORM\Query` no longer supports setting the table key. This hadn't been necessary for a while, but the last remaining use of it has been removed. This shouldn't impact anyone much.
- `\Parable\Routing\Route` received the following updates:
  - In `setUrl()`, it now prepends a '/' if it isn't provided. Now you can add a url without the prepended slash if you find that cleaner.
  - It now makes sure all methods set on it are uppercase, for more consistent matching.
  - It now receives its own name and can be retrieved by calling `getName()` on it.
  - It now has `setValues()`, in case you want to overwrite all values. These will be passed to the controller or callable, in order. This makes it possible to intercept a dispatch and inject, for example, the request or response objects in addition to the parameter values from the url.
  - It now has `createFromDataArray()`, which can be used to create a `Route` object from the same type of data set in the `Routing` file in the structure.
- `\Parable\Routing\Router` now has a `getRoutes()` method that returns all set routes. In case you, err, need that. 
- `dynamicReturnTypeMeta.json` has been added, removing the need for `/** @var \Class $var */` references in the code. This works with the dynamic return type plugin in PhpStorm. Removed the few existing references that were there.
- It's now possible to set a new config value - `parable.database.soft-quotes` - to either `true` (default) or `false`. If `true`, Parable will fake quotes for values if there's no database instance available. If set to `false`, it'll refuse to quote instead.

__Backwards-incompatible Changes__

- `Bootstrap.php` has been removed. `\Parable\Framework\App` handles its own setup now. This makes it easier to implement App without much hassle.
- `SessionMessage` has been moved from the `GetSet` component into `Framework`, as it isn't a `GetSet` instance itself but merely uses the `Session` instance.
- `\Parable\Console` no longer accepts options in the format `--option value`, but only in the following: `--option=value`. This is because if you had an option which didn't require a value, and was followed by an argument, the argument would be seen as the option's value instead.
- `\Parable\Console\App::setDefaultCommand()` now takes a command instance rather than the name, as the name would suggest. To set the default command by name, use `setDefaultCommandByName()` instead.
- `\Parable\Console\App::setOnlyUseDefaultCommand()` was added, and the boolean paramater was removed from the `setDefaultCommand/ByName()` function calls. Checked by calling `shouldOnlyUseDefaultCommand()`.
- `\Parable\Console\Command::addOption()` and `addArgument()` no longer take booleans for `required` or `valueRequired` but constants. See `\Parable\Console\Parameter` for the values. This adds the `OPTION_VALUE_PROHIBITED` possibility. Options can no longer be made required.
- `\Parable\Console\Parameter` has received several constants: `PARAMETER_OPTIONAL`, `PARAMETER_REQUIRED`, `OPTION_VALUE_OPTIONAL` and `OPTION_VALUE_REQUIRED`. This replaces the boolean functionality Parable had before. This makes it more readable.
- `\Parable\Console\Parameter::setOptions()` was renamed to `setCommandOptions()`, because the distinction is important.
- `\Parable\Console\Output::writeError/writeInfo/writeSuccess()` are now suffixed with `Block`, so `writeInfoBlock()`, etc.
- `\Parable\DI\Container::cleanName()` has been made protected. This shouldn't impact you, as you shouldn't've been using it in the first place.
- `\Parable\Framework\Dispatcher` no longer passes the Route to actions or callables as the first parameter. All parameters passed will be from the url params, in the same order as defined.
- `\Parable\GetSet\InputStream::extractAndSetData()` has been made protected. See above for why you should totally be fine.
- The interface `\Parable\Log\Writer` has been renamed and moved to `\Parable\Log\Writer\WriterInterface` for clarity and consistency.
- `\Parable\Log\Writer\Terminal` has been renamed to `\Parable\Log\Writer\Console`, because I don't know what was wrong with me when I chose 'terminal'.
- `\Parable\ORM\Model::guessValueType()` has been removed. Everything's a string now. Parable shouldn't change types for you. Honestly, when I added a method that started with `guess`, I should've doubted whether it was the right choice in the first place. Oh well.
- `\Parable\ORM\Repository::onlyCount()` has been renamed to `setOnlyCount(bool)`, as the previous name suggested that calling it would enable only count mode, other than it being a toggle.
- `\Parable\Routing\Route` has lost the ability to use typed params. Too much code for too little gain. If you need typed parameters, I suggest you figure something out for yourself.
- `\Parable\Routing\Route` no longer supports `template` for the template path, but the more correctly named `templatePath` instead. Because of this, it now checks more strictly whether valid properties are set through the Routing array. `setDataFromArray()` attempts to call setters named like the properties. Any that are not available with a setter will throw an Exception. All properties are now also `protected`.
- `\Parable\Routing\Router` now also supports adding a completely set-up `Route` object directly (or in an array), without having to pass them as arrays, through `addRoute()` and `addRoutes()`. These methods already existed, but those are now renamed to `addRouteFromArray()` and `addRoutesFromArray()`.
- The default structure's `\Routing\App` is now expected to extend the abstract base class `\Parable\Framework\Routing\AbstractRouting` and the old `Interfaces\Routing` interface has been removed. **This removes passing routes as arrays!**. See the new implementation, using the method calls on `\Parable\Framework\App` in the structure's `app/Routing/App.php_struct`.
- Two config keys were renamed: `parable.session.autoEnable` has become `parable.session.auto-enable` and `parable.app.homeDir` has become `parable.app.homedir`. The option for `init-structure` has also become `--homedir`.

__Bugfixes__

- `\Parable\Console\Output` had a bug where moving the cursors would mess with the functionality of `clearLine()`. Line length is no longer kept track of, but whether or not the line is clearable is a boolean value. Moving the cursor up/down or placing it disables line clearing, writing anything enables it again. When you clear the line, the line gets cleared using the terminal width.
- `\Parable\Console\Parameter` had a bug where providing an argument after an option with an `=` sign in it (so `script.php command option=value arg`) would see the argument as the option value and overwrite the actual value. Fixed by @dmvdbrugge in PR #31. Thanks!
- `\Parable\Console\Parameter` had a bug, where false-equivalent values passed to an option would be seen as the option being provided without a value, making the returned value `true`. Fixed by @dmvdbrugge in PR #37. Thanks!
- `\Parable\Http\Response` had a bug in `appendContent()` and `prependResponse()` where when working with arrays, empty values ended up as an empty array key, messing with json output.
- `\Parable\Filesystem\Path::getDir()` had a bug where if the filename you were trying to get a proper base-dirred path for already existed in the directory the code was run from, it would think it didn't need to and return just the provided path again.
- `\Parable\Framework\Config` had a bug where a class was referenced that doesn't exist until you've run `parable init-structure`. This has been replaced with a string value instead. Found by @dmvdbrugge. Thanks!
- `\Parable\Routing\Router` now sanitizes the Url before trying to match it, stripping html and special characters.

[Check CHANGELOG-HISTORY.md](CHANGELOG-HISTORY.md) for older Changelogs.
