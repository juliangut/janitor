[![Latest Version](https://img.shields.io/packagist/vpre/juliangut/janitor.svg?style=flat-square)](https://packagist.org/packages/juliangut/janitor)
[![License](https://img.shields.io/github/license/juliangut/janitor.svg?style=flat-square)](https://github.com/juliangut/janitor/blob/master/LICENSE)

[![Build status](https://img.shields.io/travis/juliangut/janitor.svg?style=flat-square)](https://travis-ci.org/juliangut/janitor)
[![Style](https://styleci.io/repos/43243157/shield)](https://styleci.io/repos/43243157)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/janitor.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/janitor)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/janitor.svg?style=flat-square)](https://coveralls.io/github/juliangut/janitor)
[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/janitor.svg?style=flat-square)](https://packagist.org/packages/juliangut/janitor)

# Janitor

Effortless maintenance management.

Janitor is a ready to use PSR7 middleware that provides you with an easy configurable and extensible way to handle maintenance mode on your project, because maintenance handling goes beyond responding to the user with an HTTP 503 code and a simple message.

Set several conditions that will be checked to determine if the maintenance handler should be triggered. This conditions are of two kinds, 'activation' conditions (name `watchers`) and conditions to bypass the normal execution (named `excluders`). Default watchers and excluders allows you to cover a wide range of situations so you can drop Janitor in and start in no time, but if needed it's very easy to create your own conditions.

Once Janitor has determine maintenance mode is active it let you use your handler to get a response ready for the user or you can let Janitor handle it all by itself (a nicely formatted 503 response).

> Learn more in [Janitor's page](http://juliangut.com/janitor)

## Installation

Best way to install is using Composer:

```
// Assuming composer is installed globally
composer require juliangut/janitor
```

Then require the autoload file:

```php
require_once './vendor/autoload.php';
```

## Usage

```php
use Janitor\Excluder\IP as IPExcluder;
use Janitor\Excluder\Path as PathExcluder;
use Janitor\Janitor;
use Janitor\Watcher;
use Janitor\Watcher\File as FileWatcher;
use Janitor\Watcher\Scheduled\Cron as CronWatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

$watchers = [
    new FileWatcher('/tmp/maintenance'),
    new CronWatcher('0 0 * * 0', new \DateInterval('PT2H')),
];
$excluders = [
    new IPExcluder('127.0.0.1'),
    new PathExcluder(['/maintenance', '/^\/admin/']),
];

$handler = function (ServerRequestInterface $request, ResponseInterface $response, Watcher $watcher) {
    $response->getBody()->write('I am in maintenance mode!');

    return $response;
}

$activeWatcherAttributeName = 'maintenance_watcher'; // Default is 'active_watcher'
$janitor = new Janitor($watchers, $excluders, $handler, $activeWatcherAttributeName);

$response = $janitor(
    ServerRequestFactory::fromGlobals(),
    new Response('php://temp'),
    function ($request, $response) use ($activeHandlerAttributeName) {
        $activeHandler = $request->getAttribute($activeHandlerAttributeName);
        // ...
    }
);
```

> In case a watcher is active at any given point (and so maintenance mode does) it will be attached as an attribute to the request object so it can be retrieved during execution.

## Watchers

Watchers serve different means to activate maintenance mode by verifying conditions to be met.

* `Manual` Just set it to be active. Useful to be used with a configuration parameter.
* `File` Checks the existance of the provided file.
* `Environment` Checks if an environment variable is set to a value.

```php
$manualWatcher = new \Janitor\Watcher\Manual(true);
// Always active
$manualWatcher->isActive();

$fileWatcher = new \Janitor\Watcher\File('/tmp/maintenance');
// Active if /tmp/maintenance file exists
$fileWatcher->isActive();

$envWatcher = new \Janitor\Watcher\Environment('maintenance', 'ON');
// Active if 'maintenance' environment variable value is 'ON'
$envWatcher->isActive();
```

### Scheduled whatchers

Scheduled watchers are a special type of watchers that identify a point in time in the future for a maintenance period.

* `Fixed` Hard set start and/or end times for a scheduled maintenance period.
* `Cron` Set periodic maintenance periods using [cron expression](https://en.wikipedia.org/wiki/Cron#CRON_expression) syntax.

```php
$fixedWatcher = new \Janitor\Watcher\Scheduled\Fixed('2026/01/01 00:00:00', '2026/01/01 01:00:00');
// Active only 1st January 2026 for exactly 1 hour
$fixedWatcher->isActive();

$cronWatcher = new \Janitor\Watcher\Scheduled\Cron('0 0 1 * *', new \DateInterval('PT2H'));
// Active the first day of each month at midnight during 2 hours
$cronWatcher->isActive();
```

From a scheduled watcher you can get a list of upcoming maintenance periods

```php
$cronWatcher = new \Janitor\Watcher\Scheduled\Cron('0 0 1 * *', new \DateInterval('PT2H'));
// Array of ['start' => \DateTime, 'end' => \DateTime] of next maintenance periods
$scheduledPeriods = $cronWatcher->getScheduledTimes(10);
```

> Watchers are checked in the order they are added, once a watcher is active the rest won't be checked.

_If you perform maintenance tasks periodically (maybe on the same day of every week) you may want to use either `Cron` watcher to identify the date and the time period needed, or `File` watcher to watch for a file in your system and set your maintenance process to `touch` and `rm` that file as part of the maintenance process._

_Cron watcher uses Michael Dowling's [cron-expression](https://github.com/mtdowling/cron-expression)._

## Excluders

Excluders set conditions to bypass maintenance mode in order to allow certain persons through or certain pages to be accessed.

* `IP` Verifies user's IP to allow access.
* `Path` Excludes certain URL paths from maintenance.

```php
$ipExcluder = new \Janitor\Excluder\IP('127.0.0.1');
// Users accessing from IP 127.0.0.1 are excluded
$ipExcluder->isExcluded($request);

$pathExcluder = new \Janitor\Excluder\Path('/maintenance');
// Users accessing 'http://yourdomain.com/maintenance' are excluded
$pathExcluder->isExcluded($request);

$pathExcluder = new \Janitor\Excluder\Path('/^\/admin/');
// Can also be a regex
$pathExcluder->isExcluded($request);
```

> When adding excluders consider they are checked in the same order they are included so that when an excluder condition is met the rest of the excluders won't be tested. Add more general excluders first and then more focused ones.

_Tipically you'll want to exclude your team's IPs and certain pages such as maintenance or administration zone._

## Handlers

In order to handle maintenance mode any callable can be provided to `setHandler` method given it follows this signature:

```php
function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, \Janitor\Watcher $watcher);
```

Two really basic handlers are suplied by default to cope with maintenance mode.

* `Render` Sets response with 503 code and add basic formatted maintenance output based on request's Accept header.
* `Redirect` Prepares response to be a 302 redirection to a configured URL (tipically maintenance page).

Of the two `Render` will be automatically created and used in case none is provided.

## Scheduled maintenance service

If scheduled watchers are being used they open the option to show a list of future maintenance periods, for example on a page dedicated to inform users about future maintenance actions.

```php
use Janitor\Janitor;
use Janitor\Watcher\Scheduled\Cron;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

$watchers = [new Cron('0 0 1 * *', new \DateInterval('PT2H'));];

$janitor = new Janitor($watchers);

$response = $janitor(
    ServerRequestFactory::fromGlobals(),
    new Response('php://temp'),
    function ($request, $response) use ($janitor) {
        // Array of ['start' => \DateTime, 'end' => \DateTime]
        $scheduledPeriods = $janitor->getScheduledTimes();
    }
);
```

## Examples

### Slim3

```php
use Janitor\Janitor;
use Slim\App;

$watchers = [];
$excluders = [];

$app = new App();

// Add middleware (using default Render handler)
$app->add(new Janitor($watchers, $excluders));

$app->run();
```

### Zend Expressive

```php
use Janitor\Handler\Redirect;
use Janitor\Janitor;
use Zend\Expresive\AppFactory;

$watchers = [];
$excluders = [];
$handler = new Redirect('/maintenance');

$app = AppFactory::create();

// Add middleware
$app->pipe(new Janitor($watchers, $excluders, $handler));

$app->run();
```

### Symfony's HttpFoundation

If using Symfony's HttpFoundation you can still add Janitor to your toolbelt by using Symfony's [PSR HTTP message bridge](https://github.com/symfony/psr-http-message-bridge)

An example using Silex

```php
use Janitor\Janitor;
use Silex\Application;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Request;
use Zend\Diactoros\Response;

$janitor = new Janitor();

$app = new Application;

$app->before(function (Request $request, Application $app) use ($janitor) {
    $response = $janitor(
        (new DiactorosFactory)->createRequest($request),
        new Response('php://temp'),
        function ($request, $response) {
            return $response;
        }
    );

    return (new HttpFoundationFactory)->createResponse($response);
});

$app->run();
```

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/janitor/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/janitor/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/janitor/blob/master/LICENSE) included with the source code for a copy of the license terms.
