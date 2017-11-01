# Parable PHP Framework Changelog History

This file contains the changelog history for Parable. Changelogs pertaining to versions two major versions before the current active version are moved here.

### 0.10.3

__Bugfixes__
- `\Parable\Mailer\Mail` was not handling headers properly.

### 0.10.2

__Bugfixes__
- `removeTemplateVariables` logic re-implemented on `\Parable\Framework\Mailer\Mail`'s `resetMailData()`

### 0.10.1

__Bugfixes__
- Reference to property `$templateVariables` in `\Parable\Mailer\Mail` fixed.

### 0.10.0

Note: Breaks backwards compatibility in some instances, and based on your implementation might require small tweaks or none whatsoever. By reading the following
changelog carefully it should be trivial to fix any incompatibilities.

__Changes__
- `\Parable\Auth\Authentication` has been moved to `\Parable\Framework\Authentication`, since it cannot function without packages from `Framework` and `Http`.
- `\Parable\Auth\Rights` has been moved to `\Parable\Tool\Rights`, since by itself it does not warrent an `Auth` namespace.
- `\Parable\Mail\Mailer` has also been improved:
    - The main class been simplified and all template logic has been moved to `\Parable\Framework\Mail\Mailer`, since those require external logic.
    - `\Parable\Framework\Mail\Mailer` now uses a `GetSet` implementation (`\Parable\Mail\TemplateVariables`) for its template variables and requires a full path for `loadTemplate` calls.
    - `\Parable\Mail\Mailer` has gained `requiredHeader`, to distinguish between headers it wants to enforce itself and ones set by the dev.
    - `\Parable\Mail\Mailer` has gained `getSubject()`, `getBody()`, `getHeaders()`, `getRequiredHeaders()`.
    - `\Parable\Mail\Mailer` has gained `resetMailData()`, `resetRecipients()`, `resetSender()`, `reset()` (resets all but sender).
- All classes previously using `Routes` as a namespace now use `Routing` to make the namespace more consistently singular.
- `\Parable\Console` has once again gotten some love:
  - `\Parable\Console\Command` namespace has been added, with 2 commands: `Help` and `Init`.
  - It's now possible to add your own commands to the `parable` command, as shown in structure's `\Config\App` (`$config->get('console.commands')`). 
  - `\Parable\Console\App` is now always available to Commands, by setting it through `setApp()`.
  - `parable.php` has been moved up one directory.
- `\Parable\Events` namespace has been changed to `\Parable\Event` for consistency.
- `\Parable\Http\Values\GetSet` has gained the following methods: `getAllAndReset()`, `getAndRemove($key)`, `reset()` and `count()`.

__Bugfixes__
- `\Parable\Framework\View` list of `@property` tags updated, since some classes were missing.

__Miscellaneous__
- Where logical and readable, double-quote {}-style concatenation added.
- Comments improved in places.

### 0.9.8

__Changes__
- `\Parable\Auth\Authentication` no longer demands a `\Model\Users` object, but defaults to `\Model\User` and allows another class to be set.
- Since the Auth system now has a user-overwritable user class name, it no longer calls `initialize()` in its `__construct()`. Easiest way of both overwriting and initializing the Auth system is by using an `Init\Auth` class to do so.

### 0.9.7

__Changes__
- `\Parable\Mail` has been added, a very basic wrapper for the native `mail()` function. For any kind of elaborate mailing, this probably won't suffice. But for simple one-off mail sending, it should work relatively well.

### 0.9.6

__Bugfixes__
- `\Parable\Routing\Route` now injects parameters correctly. This fixes a rare bug where a url with `/a/{id}/b/{name}` would fail if both `$id` and `$name` had the same value.

### 0.9.5

__Changes__
- It should now be easier to build your own Console Commands. You can extend the `\Parable\Console\Command` class and implement `run()`. This will automatically get called. If there's no `run()` defined, the base class will attempt to run the callback if it's defined.
- `\Parable\Console\App` has gained `getCommands()`, which will help when the `\Parable\Console\Commands\Help` class lands, probably in 0.9.6.
- `\Parable\Console\Input` has gained `getHidden()`, which hides the characters the user types in. Only works on nix systems.
- `\Parable\Console\Input` has gained `getYesNo(bool)`, to ask Y/n or y/N questions. 'Y/n' (passing `true`) will return `true` on either 'y' or an empty string. 'y/N' (passing `false`) will _only_ return `true` on a 'y'.
- `src/parable` has been updated to use the above logic.

__Bugfixes__
- `\Parable\Auth\Authentication` was unable to return the user upon authenticating. This was because `initialize()` actually loaded the User model, but was only called on construct. `getUser()` now calls `initialize()` if there's no user set yet.

### 0.9.4

__Changes__
- Added `generatePasswordHash()` to `\Parable\Auth\Authentication`.

__Bugfixes__
- Fixed issue in `\Parable\Auth\Authentication` where without authentication data, a non-existing array key was read. Now that's a reason to say someone isn't validated. This only happened when calling `authenticate` and the password validating correctly.
- `\Parable\Framework\Dispatcher` can now handle nested namespaced controllers for default template files. So `Controller\Subnamespace\Home` will attempt to load `app/view/Subnamespace/Home/action.phtml`.

### 0.9.3

__Bugfixes__
- `\Parable\Http\Values\GetSet` incorrectly set the local resource when using `setAll()`.

### 0.9.2

__Changes__
- Parameters from a Route are now directly passed into actions. No more getting it from the Route through `getValue()`. See the new `\Controller\Home` example action for how to use it. The first parameter is still the `\Parable\Routing\Route` instance associated with the request.
- Parameters passed to a Route can now be typecast in the url itself. `{param:int}`, `{param:string}`, `{param:float}`. Other types are, at the moment, all string values.
- Methods for routes should now be passed as an array. Passing it as a string is allowed for now, but will be removed the next time the version is bumped to either 0.10.0 or 1.0.0, whichever happens first.
- Many public functions have been turned protected if public access wasn't required or desired.
- `\Parable\Routing\Route` has gained a `getValues()` method.
- `\Init\Example` has been expanded, showing a way to hook into the 404 event, as well as that DI is available as usual.

__Bugfixes__
- HTTP code 200 is now explicitly set on a successful dispatch.
- The full URL is now passed to the `parable_http_404` hook, rather than just the partial.
- A bug in a file that's too embarrassing to mention.

### 0.9.1

__Changes__
- `\Parable\Console` now supports Options. Check `\Parable\Console\Command::addOption(...)` for how to use it. You can use `\Parable\Console\Parameter::getOption('string')` to get the Option's value. If it doesn't have a value given and no defaultValue, it'll return `true`.

__Bugfixes__
- `\Parable\ORM\Database` now overwrites __debugInfo so it won't be var_dump'ed/print_r'ed into giving out database credentials.
- `\Parable\ORM\Database::NULL_VALUE` has been added to set a NULL value that'll actually set a SQL field to NULL. Before the string value 'null' would do this, but that's unfair to all the people who have Null as their last name. Any other empty (but not 0) value will skip the field when saving to the database.

### 0.9.0

__Note: This version might be incompatible with previous versions. If you've ever set specific `->select()` values on a `Query` object, you'll have to rewrite those calls to pass an array of items rather than a comma-separated string.__

It is the intention for the 0.9.x branch to be the last pre-release branch before a 1.0.0 release. For this, the focus is on bug fixes and some refactors that will solve long-standing issues or shortcomings in Parable subsystems. 0.9.x will also see Documentation start taking shape. Development of this branch might be somewhat slower due to this, but it's all about working towards getting out of pre-release. Exciting, if you ask me :)

__Changes__
- `\Parable\ORM\Query` has been upgraded significantly:
  - It now requires an array with values for select, so they can all be prefixed with the table name and quoted appropriately.
  - It no longer requires a database connection to build a query, but when no database is present, it does basic quoting instead of real quoting. Only for testing and dev purposes, not for production!
  - All queries now have their table names added to the field names, to prevent ambiguity in joins. In join-less queries, it can't hurt.
- `\Parable\Cli` has been replaced by `\Parable\Console` and everybody rejoiced. See `parable.php` for a simple implementation. It still needs work, but it's a start.
- `\Parable\DI\Container::store` now allows passing a custom name if you want to. This makes it possible to store a specific instance under a specific name (say, an interface name).
- Config files no longer implement `\Parable\Framework\Interfaces\Config` but extend `\Parable\Framework\Config\Base`. This serves the same purpose but takes away the need to redeclare `getSortOrder` every time.
- `Routes.php` has been moved to `Routes\App.php` and is now in the namespace `\Routes`. This satisfies PSR-2 requirements and looks nice. Also makes it possible to set up your routes in separate files and order them that way.
- Package-specific `Exception` classes have been added to `DI`, `Framework`, `ORM` and `Routing`.
- `array` type hints in method parameters have been consistently added where applicable.
- Docblock type hints have been improved and, where needed, fixed.

__Bugfixes__
- Due to the changes in `\Parable\ORM\Query`, joins should now work properly. `join()` has been replaced with `innerJoin()`, and `leftJoin()`, `rightJoin()` and `fullJoin()` have been added.
- `parable.php` was not copying the `Init/Example.php` file, which isn't helpful. Fixed now.
- There was one reference to `\Parable\ORM\Query::select()` which was still passing a string. This has been altered to pass an array instead.

### 0.8.18

This one's for all y'all Windows users!

__Bugfixes__
- `\Parable\Http\Output\Json` now checks whether `$content` is an array.
- `\Parable\Filesystem\Path::getDir()` now replaces '/' with DIRECTORY_SEPARATOR, so windows users shouldn't run into issues anymore.

### 0.8.17

__Bugfixes__
- Additional fix for `\Parable\Http\Url` absolute baseurls.

### 0.8.16

__Changes__
- Practice what you preach: PSR-2 is in. Mostly. `Bootstrap.php` is currently not a-okay with Codesniffer, and neither is the provided `structure/app/Routes`. But I can live with those for now. Changing Routes (specifically) would warrant 0.9 and there's more important stuff to get done!
- Soft deletes are out again, because that was a step further than I feel Parable needs to go.

__Bugfixes__
- `\Parable\Framework\App::loadInits()` didn't give a crap whether a file was a php file or not. Now it does, as it should.
- `\Parable\Http\Url` got absolute baseurls, but also left in an extraneous `/`. This over-enthusiasm is now fixed.

### 0.8.15

__Changes__
- The Session is still automatically started, but there's a way of disabling this. Check out `app/Config/App.php`. There's a session.autoEnable setting. If you leave it out, it's assumed to be true and the session will be started automatically. If you set it to `false`, no session unless you start it yourself.
- 4 hooks have been added for you to play with: `parable_session_start_before`, `parable_session_start_after`, `parable_config_load_before` and `parable_config_load_after`. Enjoy! Remember: to figure out all hooks you can work with, just search the codebase for `->trigger`.
- Models and Repositories now support `is_deleted` for soft-deletes. If your model has an `is_deleted` property, instead of deleting data from the database, an `is_deleted` field in your database's row is flipper from 0 to 1. If you do this and you don't have an `is_deleted` field, expect failure.
- If you use soft deletes, you can also `$model->undelete()` them. This will restore them to `is_deleted` set to 0.
- To make working with soft deletes somewhat easier, you can now tell the repository to by default filter using the `is_deleted` field by calling `$repository->setFilterSoftDeletes(true)`.
- `\Parable\Http\Url` now gives back the absolute baseurl rather than a relative one. This means it now includes the http method and full domain.

__Bugfixes__
- `\Parable\Framework\Config` didn't give a crap whether a file was a php file or not. Now it does, as it should.

### 0.8.14

__Bugfixes__
- I guess I never tried offset before, because limit and offset were the wrong way around, hah! And this is why it _really_ ain't final, people! :D

### 0.8.13

__Bugfixes__
- `\Parable\ORM\Query` had a rather well-hidden bug, where offset wasn't actually the offset but the limit value. So a limit/offset of 5,0 would end up being 5,5. And this is why it ain't final, people! :D

### 0.8.12

__Changes__
- `\Parable\Http\Response` has gained a list of HTTP codes with their matching text codes. Setting `404` now properly gives `404 Not found`.
- `\Parable\Http\Response\Output` objects now have an `init` and a `prepare`. `init` is called when the Output class is set, and `prepare` just before output is sent to the browser. This allows changing of content type before output.

__Bugfixes__
- `\Parable\Http\Response\Output\Html` did not actually implement `\Parable\Http\Output\OutputInterface`, but it wasn't noticed since the setter wasn't used to set it as default. Silly mistake.

### 0.8.11

__Changes__
- `\Parable\DI\Container` now allows storing a class. This makes it possible to instantiate it, alter it (inject data) and store it for further `\Parable\DI\Container::get` requests.
- `revokeAuthentication()` was added to `\Parable\Auth\Authentication`, so that it's possible to log someone out from outside the class.
- `\Parable\Http\Request` now automatically loads the headers of the request. You can get at 'em by using `getHeader($key)` or `getHeaders()`
- `\Parable\Http\Response` also received some love, gaining `setHeader($key, $value)`, `getHeader($key)` and `getHeaders()` as well.

__Bugfixes__
- `\Parable\Framework\Dispatcher` was looking at the wrong variable for a Route's `template` value. Fixed now. Silly mistake.

### 0.8.10

__Changes__
- Output types have been added. By default the `\Parable\Http\Output\Html` class is used, but to set the `Json` Output type, simply call `$response->setOuput(new ...)`, passing a new instance of the appropriate output type.

__Bugfixes__
- Small bug in join fixed.

### 0.8.9

By implementing, you find the bugs and shortcomings. This is an update to fix those.

__Changes__
- `\Parable\Auth\Authentication` has been added and relies on the `\Model\User` class offered in structure. To use Authentication, base your User class on that one.
- `\Parable\Http\SessionMessage` has been added, which allows for passing messages between sessions.
- `\Parable\Framework\Toolkit` now has a `redirectToRoute($routeName)` method, which is super handy. Using route names instead of urls allows less breaking if you change a url.
- Toolkit also gained `getFullRouteUrlByName($name, $parameters)`, which links through to Router's `getRouteUrlByName`, which eventually calls a Route's `buildUrlWithParameters`. This allows, in views, the use of `$this->toolkit->getFullRouteUrlByName($name, $parameters)`, which will give a full url to the route with the parameters filled in.
- `\Parable\Framework\View` now offers access to database and query, in case you want to mix responsibilities.
- `\Parable\Http\Url` now has a `getCurrentUrlFull` method, which will return the current url as a full public url (http(s)://blahblah) rather than just the matchable part (i.e. 'index').
- `\Parable\ORM\Repository` now accepts `($key, $comparator, $value)` for `getByCondition`, to be in line with `\Parable\ORM\Query`'s where/join parameters. `getByConditions` still expects an array of arrays, which in turn are `[$key, $comparator, $value]`

__Bugfixes__
- `\Parable\Framework\App` now automatically starts the session.
- Shoutout to `coworkers` ;)
- `\Parable\Framework\Config` now only tries to load Config files that implement the `\Parable\Framework\Interfaces\Config` interface.
- `\Parable\Http\Request` now properly returns on `isMethod`. I was inadvertently inverting a boolean value while trying to cast as bool while already having a bool. Oops!
- In `\Parable\Http\Url`, after a redirect, we now die to make sure the redirect is honored. 

### 0.8.8

__Bugfixes__
- Weird sudden bug in `structure/public/index.php` that I can't place. Eh. Fixed!

### 0.8.7

This is merely a maintenance update, to officially up the minimum supported version of php to 5.6. Since 5.5 went EOL 3 weeks ago, it can't be considered secure anymore. It would be a shame not to be able to use modern php functionality for a version that will no longer receive updates. So 5.6+ it is.

### 0.8.6

From time to time, clean-up is needed. This is one of those moments, where an unsexy but necessary overhaul was done for \Parable\ORM\Query.

__Changes__
- Removed check in `Bootstrap.php` to see if the composer autoload exists. By the time you've gotten to that point, it's almost inevitable.
- Removed option to disable quoteAll on either `\Parable\ORM\Database` or `\Parable\ORM\Query` level. Sssh, it's better this way.
- To help in quoting everything correctly, a `quoteIdentifier()` method has been added to `\Parable\ORM\Database`. PDO doesn't offer a way to quote with backticks, so there ya go.
- `\Parable\ORM\Query` changes:
    1. Conditions `('id = ?', 1)` have been replaced with key/comparator/value `('id', '=', 1)`. This allows running `quoteIdentifier()` on keys.
    2. This impacts `where()` and `join()` calls.
    3. To simplify the class, `buildJoins()`, `buildWheres()`, `buildOrderBy()`, `buildGroupBy()` and `buildLimitOffset()` have been added, which do exactly as they're named.
    4. The above functions will call `buildCondition($conditionArray)` to build conditions. `buildCondition` now also correctly handles `IN` and `NOT IN` comparators by escaping all values separately. The correct call to add an `IN` where is as follows: `$query->where('id', 'not in', [1,3,5]);`. All other types of comparison are scalar. To use an `IS NULL` or `IS NOT NULL`, do it like this: `$query->where('id', 'IS NOT NULL');`. The `$value` parameter is optional for this reason.
    5. All queries now nicely end with a `;` character, for copy-pasting reasons.
- `\Parable\Framework\Repository` now uses the new conditions.

__Bugfixes__
- In `\Cli\App`, parenthesis were placed wrong and additional mkdir params were being ignored. Kinda surprised it still worked, but at least now it should work even better.

### 0.8.5

__Bugfixes__
- Non-nested config values now _also_ return null if they don't exist, rather than the Config freakin' out. I think I got this.

### 0.8.4

__Changes__
- Added Init scripts. Check app/Init/Example.php for details on what you'd use these for.
- The Dispatcher now uses output buffering. Any content returned from controllers/closures is _appended_ to it.
- Database now always quotes all. This is due to reserved keywords in both sqlite/mysql. This should not impact any existing scripts.

__Bugfixes__
- Nested config values (such as 'app.title') now return null if they don't exist, rather than the Config freakin' out.
- Toolkit now (as originally intended) ignores Cli\App. It was confusing which App class to use. Cli\App should never be available in a web context.

### 0.8.3

__Changes__
- Getting all the little details right.
- Moved Parable version to \Parable\Framework\App, as it should remain available even if the config is changed.
- Moved Able > Cli.
- Removed Error folder from mkdir list, as it's no longer included.
- Added \Parable\Framework\App to View magic properties, so it's available there as well.

### 0.8.2

__Changes__
- README.md updated

### 0.8.1

__Changes__
- README.md now has the new install instructions (through composer and packagist)

### 0.8.0

__Note: This version is *completely* incompatible with previous versions and is basically a rewrite. I told you not to expect backwards compatibility just yet ;)__

__Changes__
- Honestly, too many to mention. Everything's been reordered, some parts have been completely rewritten. It's incompatible. Documentation will show up soon, but installing it and following the instructions in the readme should help you get started.

### 0.7.3

__Changes__
- Using composer's autoload primarily from now on. This is in preparation of making components an actual composer package. This has no effect on 0.7.x projects, but it does require you to have composer.

### 0.7.2

__Bugfixes__
- Haste is waste. Fixed properly now. It's a Sunday and I should've had more coffee by now ;)

### 0.7.1

__Bugfixes__
- null was being passed to Tool->setRoute(), which since very recently requires an array, causing a fatal error instead of a 404. Fixed.

### 0.7.0

__Note: This version breaks backwards compatibility!__

__Changes__
- Since SessionMessage depends on an instantiated & set to 'session' resource GetSet, it shouldn't be a Component. It's been moved to \Devvoh\Parable. Because this means a namespace change, it's yet another minor version bump. Goes quickly.
- To make the module Routes file simpler, the more framework-y functionality (DI & the module adding) has been moved to a new class - \Devvoh\Parable\Routes. This means yet another backwards incompatible change. I'm on a roll.
- Comments have been improved significantly. Handy.
- index.phtml mapped $this to \Devvoh\Parable\App, but it should now map to \Devvoh\Parable\View
- The magic methods in \Devvoh\Parable\View are gone again. Though they worked fine, they felt out of place and inconsistent with how the rest of Parable now approaches its DI components. They've been replaced with magic properties that do the exact same thing. In a view, you can use $this->tool->method(), etc.
- set_exception_handler function has been moved up, so it will also catch Autoloader/DI exceptions.
- Property definitions now no longer explicitly set to null. Order of properties has been made consistent: DI properties > defined properties > undefined properties.
- Some small fixes where false was being returned where a value was expected. These now return null.
- Vestigial properties have been removed from App, since the functionality has been moved to Tool.
- Session management methods have been moved to \Devvoh\Parable\Session and out of \Devvoh\Components\GetSet, as they should be.
- SessionMessage has lost initSession and gained a DI.
- Cli has been reworked, now offers cll (clear line), cr (return to beginning of line) as well as colors.

### 0.6.0

__Note: This version significantly breaks backwards compatibility!__

__Changes__
- APP IS DEAD. ALL HAIL APP.
- Because App is gone, much of the changelog of 0.5.0 is redundant, but I have included what's still relevant.
- \Devvoh\Components\DI has been added. This is why App could go. Parable now has a barebones dependency injection system and uses it throughout. It attempts to keep track of class dependency hierarchy to prevent cyclical references. It should throw an Exception when A requires B requires A...etc.
- Views now can no longer simply re-route all function calls to App. Therefore, magic methods have been added to \Devvoh\Parable\View to allow the ->getXXX calls to still work (they now go through DI, though), and really only affects the methods previously directly called on App (getUrl, createRepository, etc.), which are almost all moved to \Devvoh\Parable\Tool
- Although this major refactor has been tested (and found to work correctly) on one project, it's entirely possible bugs may still be hidden.
- 'Tool' functionality has been moved into \Devvoh\Components\Tool.
- View and Config have been moved into vendor/Devvoh/Parable.
- \Devvoh\Parable\Dispatcher has been added, and actually executing the route is done there
- On Dispatcher, the execute method has been reworked significantly, for better readability and more efficient code. Now also supports using a view key on a controller route, which will be looked for before looking for an auto-generated view path.
- Added /app/modules/[module]/Init/ functionality. All php scripts in this directory will be loaded & instantiated at the end of $app->boot(), allowing the developer to plug into events as soon as is possible. At this point in Parable's runtime, all config is loaded and the session and database are available. Included is a Hooks init script, which adds 3 hooks. Init scripts can be either sorted (lowest order first) or unsorted.
- \Devvoh\Components\Getset now has a method setMany($array), which will set all key/value pairs in the passed array and add them to the resource.
- \Devvoh\Components\Hook and \Devvoh\Components\Dock now support global events, using the '*' wildcard event name. Any closures added to this event will be called on every trigger. Handy for debugging. Even if there's no valid events on a trigger, the global event will still be called.
- \Devvoh\Components\Hook and \Devvoh\Components\Dock now pass back the event they were triggered with, as the first parameter to the closure. The payload is now in second place, since it's optional.
- GetSet has a remove(key) method now, which sets the value to null.
- GetSet also gained regenerateSession, which will at a later moment be moved to the Parable\Session class which implements GetSet.
- Version is no longer stored in 'version' but directly on the App class as a property.
- Added a Request Component to offer base functionality on dealing with the request (most useful: isPost(), etc.)
- Added an Auth class to Parable for base user rights. It's really straightforward but can always be extended or not used. This will be built out at a later date.
- Added ->returnOne() and ->returnAll() to Repository, which will return the first result only, preventing the need for either manual current() calls or [0].
- In Repository, orderBy and limit are now implemented on getQuery, which enables it everywhere.
- All Cli functionality removed until Cli has been refactored.
- Training wheels are off. Soft requirement for parameters (returning falls on required parameter null values) is out and hard requirements are in. Also made type hinting more strict. In some cases, it's no longer possible to pass either a string or an array, casting the string to array, it'll always need an array instead.

__Bugfixes__
- GetSet no longer resets the localResource when using setResource. Not only does this fix a bug where using setResource multiple times would clear it every time, it also makes it possible to use, for example, App::getParam()->setResource('headerJs')->getAll(). This makes Param even more powerful and useful. Param does, however, remember the last set resource, so switching is necessary whenever you want a different resource.
- display_errors was set to 1 by default in Bootstrap.php. Now set to 0 as it should.
- Entity returned an empty instance of itself if it couldn't find a suitable model. It should return null instead, which it now does.
- Entity's handling of null values was flawed. Now it'll keep a null value only if the value is string 'null'
- Router Component at a certain point picked up a bug in returning params, which has now been fixed.
- App now picks up on sqlite requiring a path for the location. Now does so only in case of sqlite.
- Reverted Param handling in Dispatcher to a foreach since they don't use $key => $val but [key] => $key, [val] => $val. My bad.
- Repositories now attempt to fetch separately from handling the result, preventing odd errors if the query is incorrect.

### 0.4.1

__Changes__
- Components/Database and Components/Query now have a property (and appropriate get/set methods) to set whether 'all' should be quoted.
- Closures should now return their value, after which their return value is added to the Response content.

__Bugfixes__
- Since NULL values are no longer automatically skipped (due to bug fix in 0.3.3), query was trying to set the id even on inserts. sqlite didn't care, but mysql does. TableKey is now always skipped in inserts.
- MySQL mode now works.

### 0.4.0

__Changes__
- Renamed Fluid to Parable. The reason is that the Flow PHP framework has a component called Fluid, which is their template parser. As such, it's become clear that this little project required a new name. As this renames all classes and even some directories, it breaks backwards compatibility. Given that parable is still pre-release software, semantic versioning isn't completely in play yet and it merely raises the version to 0.4.0.

### 0.3.5

__Changes__
- Small one, but removed composer.json again. No use for Parable, which will need an installer like symfony & laravel. For now just releases on github are enough. But I do know how to do it now, so that's something.

### 0.3.4

__Changes__
- Response->sendResponse() now exits and keeps track of whether it's already output the response. This is to prevent it being called double. As soon as sendResponse() has been called, the application stops.
- Response no longer has a __destruct method. If you die/exit, it dies/exits without attempting to output the buffer.
- Moved App logic from index.php into App::run(), which used to be boot() but now also does the routing. Later on, split this into multiple functions.
- App::executeRoute now looks for the view parameter on a route definition regardless of closure or not. This allows overruling the auto-generated template or simply using a specific template if the auto-generated template doesn't exist.
- Added Parable.php cli interpreter & \Devvoh\Parable\Cli class to handle the logic behind Parable.php, see app/modules/App/Cli/Index.php for current implementation (subject to change).
- Added some more information to the index view file.
- Added composer.json (experimental)

__Bugfixes__
- View templates' output now gets sent to the Response Component and appended to the response. Now onlyContent=true stops all echoes and other direct output from php files.
- App::createEntity() now returns an empty Entity object if the requested entity can't be found. This should prevent strange errors down the road (for example, due to App::createRepository() failing to call methods on the entity)
- Entity's id property is now public. It doesn't need to be protected and this prevents IDE mismatches when directly interacting with the property.
- Boot & autoloader now no longer work on heavily customized path logic. Now use a properly set BASEDIR constant.

### 0.3.3

__Changes__
- Added a basic Mailer Component.
- Added App::getCurrentUrl() to App class. Super useful for forms.
- App::CreateRepository() now calls App::createEntity(), which looks in all modules instead of just the current one.
- Config Component now has getBool() function for true/false settings.
- Repository class now also supports orderBy & limit/offset.
- Query Component improved.
- Simplified the doc blocks.
- Improved the Response object somewhat, but it still needs a lot of work.

__Bugfixes__
- Fixed small bugs in Entity class.
- Query Component NULL bug fixed (empty string was being inserted/updated instead of NULL value)
- Bug in SessionMessage Component fixed where upon init and the session key not existing, an undefined index notice would appear.

### 0.3.2

- The Autoloader has been moved into an Autoloader component to be expanded upon as needed. This also makes it possible to add multiple, differently configured Autoloaders when needed.
- All references to ob_* output buffering functions have been moved into the Response object and are now called from there. This makes it clearer when output buffering is used and what type.
- Default output buffering has been changed from using ob_gzhandler to regular, since ob_gzhandler would cause flicker before first byte sometimes. I need to look into this before enabling it again.
- app\modules\Base has been renamed to app\modules\Core since it functions as a 'core' for completely standard functionality such as errors.
- Removed \Devvoh\Parable\Controller as base since it'd be unwise to alter this as a developer building on top of Parable. An update of Parable would overwrite it or an annoying merging issue would arise. Replaced with user-alterable \Core\Controller\Base.
- Added a number of currentModule-related functions to App, most important of which is getModuleFromPath, which is used by Routes.php to determine the module to set on all the routes instead of having to manually set it.
- Changed the 'magic' references to App in index.phtml to self:: instead of $this for consistency's sake.Both still work, to allow the developer to decide which one they'd prefer.
- \Devvoh\Parable\App\View's _call magic method has been updated to use call_user_func_array to properly pass all parameters as they should.
- App::start() has been renamed to App::boot() because it looks nicer. For some reason this is important to me.
- Speaking of pretty things, redesigned the default 'hello' page to be more modern and playful.
- Many less important for the end user improvements (reworked almost all comments, cody style fixes, etc.)

### 0.3.1

- Controllers such as Home\Test (example provided) now work properly, allowing nesting for more fine-grained control over what goes where.
- Removed require_once in App::executeRoute, since the new namespace setup allows the regular autoloader to pick up on controller classes.
- Copyright notices in doc blocks & LICENSE updated, since (of course) it's 2016 now!

### 0.3.0

- Modules now use their own namespaces. app/modules/App/Controller/Home.php would use \App\Controller and Home as class.These are autoloaded by the same PSR-4 compatible autoloader that loads the Components and Parable assets from vendor/.
- enableDebug() has been moved into App.php, to de-clutter index.php even more.

### 0.2.8

- App::redirect($url) will now look if the url is an absolute one, and if not, runs it through App::getUrl($relativeUrl) to get a proper Parable-relevant url. This allows for safe use of App::redirect('/');
- Response content types can now be set by either the shorthand (js) or the full string (application/javascript).
- For those few App::methods() that take a parameter, which is always a maximum of 1, View now recognizes that and passes it as a single variable instead of the usual array.

### 0.2.7

- Repository now has an onlyCount flag, which can be set using Repository->setOnlyCount(bool). If this is set to true, all selects will return an integer corresponding to the number of rows found. All types of Repository calls can be made, getByConditions as well.
- Since controllers sometimes need to extend one another, modelTypes were added. These are, by default, [controller, model], but can be extended with the config key 'model_types_add' (comma-separated list). If you want to autoload test_model, it'll recognize 'model' in the classname and know it's a modelType we know. It'll look in [activeModule]/[modelType]/test.php, where the class is, of course, test_model. By extending this, you can make any type of class load automatically.

### 0.2.6

- Added Devvoh\Component\Validate and the required assets on Devvoh\Parable\Entity
- On Entity->save(), id is now added to the entity for updates/saves later on or to add to other entities
- Quoting table names due to common terms conflicting with sqlite