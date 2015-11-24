[![Latest Version](https://img.shields.io/packagist/vpre/juliangut/janitor.svg?style=flat-square)](https://packagist.org/packages/juliangut/janitor)
[![License](https://img.shields.io/github/license/juliangut/janitor.svg?style=flat-square)](https://github.com/juliangut/janitor/blob/master/LICENSE)

[![Build status](https://img.shields.io/travis/juliangut/janitor.svg?style=flat-square)](https://travis-ci.org/juliangut/janitor)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/janitor.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/janitor)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/janitor.svg?style=flat-square)](https://coveralls.io/github/juliangut/janitor)
[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/janitor.svg?style=flat-square)](https://packagist.org/packages/juliangut/janitor)

# Janitor

Effortless maintenance management.

## Installation

Best way to install is using Composer:

```
php composer.phar require juliangut/janitor
```

Then require_once the autoload file:

```php
require_once './vendor/autoload.php';
```

## Usage

Provide conditions for maintenance activation (with _watchers_) and conditions to bypass maintenance mode (with _excluders_)

```php
use Janitor\Watcher\File as FileWatcher;
use Janitor\Watcher\Cron as CronWatcher;
use Janitor\Excluder\IP as IPExcluder;
use Janitor\Excluder\Path as PathExcluder;
use Janitor\Janitor;

$watchers = [
    new FileWatcher('/tmp/maintenance'),
    new CronWatcher('0 0 * * 0', new \DateInterval('PT2H')),
];
$excluders = [
    new IPExcluder('127.0.0.1'),
    new PathExcluder(['/maintenance', '/admin']),
];

$janitor = new Janitor($watchers, $excluders);

if ($janitor->inMaintenance() && !$janitor->isExcluded()) {
    $activeWatcher = $janitor->getActiveWatcher();

    // Handle maintenance mode
}
```

Or allow Janitor to take care of maintenance mode according to the selected `Strategy`, if no strategy is provided `Render` strategy will be used outputing basic headers and mainenance HTML page.

```php
$janitor = new Janitor($watchers, $excluders);

if ($janitor->handle()) {
    die; // Headers and content already sent
}
```

## Watchers

Watchers serve different means to activate maintenance mode by verifying conditions to be met.

* `Manual` Just set it to be active. Useful to be used with a configuration parameter.
* `File` Checks the existance of the provided file in the system.
* `Environment` Checks if an environment variable has a predefined value.

```php
$inMaintenance = true;
$manualWatcher = new \Janitor\Watcher\Manual($inMaintenance);
// Always active
$manualWatcher->isActive();

$fileWatcher = new \Janitor\Watcher\File('/tmp/maintenance');
// Active if /tmp/maintenance file exists
$fileWatcher->isActive();

$envWatcher = new \Janitor\Watcher\Environment('maintenance', 'ON');
// Active if 'maintenance' environment variable value is 'ON'
$envWatcher->isActive();
```

Watchers are checked in the order they are added, once a watcher is active the rest won't be tested against.

### Scheduled whatchers

Scheduled watchers are a special type of watchers that identify a point in time in the future for a maintenance period.

* `Fixed` Hard set start and end times for a scheduled maintenance period.
* `Cron` Set periodic maintenance periods using [cron expression](https://en.wikipedia.org/wiki/Cron#CRON_expression) syntax.

```php
$fixedWatcher = new \Janitor\Watcher\Fixed('2016/01/01 00:00:00', '2016/01/01 01:00:00');
// Active only 1st Januarry 2016 for 1 hour
$fixedWatcher->isActive();

$cronWatcher = new \Janitor\Watcher\Cron('0 0 1 * *', new \DateInterval('PT2H'));
// Active the first day of each month at midnight during 2 hours
$cronWatcher->isActive();
```

_If you perform maintenance tasks periodically (maybe on the same date every week) you may want to use either `Cron` watcher to identify the date and the time period needed, or `File` watcher to watch for a file in your system and set your maintenance process to `touch` and `rm` that file as part of the maintenance process._

_Cron provider uses Michael Dowling [cron-expression](https://github.com/mtdowling/cron-expression) package._

## Excluders

Excluders set conditions to bypass maintenance mode in order to allow certain persons through or certain pages to be accessed.

* `IP` Verifies user's IP to allow access.
* `Path` Excludes certain URL paths from maintenance.

```php
$ipExcluder = new \Janitor\Excluder\IP('127.0.0.1');
// Users accessing from IP 127.0.0.1 are excluded
$ipExcluder->isExcluded();

$pathExcluder = new \Janitor\Excluder\Path('/admin');
// Users accessing http://yourdomain.com/admin are excluded
$pathExcluder->isExcluded();
```

When adding excluders consider they are checked in the same order they are included so that when an excluder condition is met the rest of the excluders won't be tested. Add more general excluders first and then more focused ones.

_Tipically you'll want to exclude your team's IPs and maintenance and administration pages._

### Excluder providers

Excluders extract information from providers to test if the condition is met. Two providers are included, one to identify user's IP and one to determine current URL path.

Default excluder providers are very simple, but you can use your own (on excluder creation) if you consider it appropriate by implementing `Janitor\Provider\IP` and `Janitor\Provider\Path` respectively.

## Strategies

Two strategies are suplied by default to handle maintenance mode if Janitor is allowed to do so.

* `Render` Will output HTTP headers and a basic formatted HTML maintenance page.
* `Redirect` Browser will get redirected to a configured URL (tipically maintenance page).

`Render` strategy is the default used if none provided and Janitor handles maintenance mode. It's very simple and most probably you would want to replace it by your own maintenance page. You can do it by implementing `Janitor\Strategy`.

```php
$janitor->setStrategy(new YourAwesomeStrategy);
```

## Integration

### Slim3

```php
use Janitor\Janitor;
use Janitor\ScheduledWatcher;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;

// Initialize Janitor with watchers and excluders
$janitor = new Janitor($watchers, $excluders);

// Add middleware
$app->add(function(RequestInterface $request, ResponseInterface $response, callable $next) use ($janitor) {
    if ($janitor->inMaintenance() && !$janitor->isExcluded()) {
        $watcher = $janitor->getActiveWatcher();

        // Handle maintenance mode
        $message = 'Maintenance mode is active';
        if ($watcher instanceof ScheduledWatcher) {
            $message = 'Maintenance mode is active until' . $watcher->getEnd()->format('Y/m/d H:i:s');
        }

        $body = new Body;
        $body->write($message);

        return $response->withStatus(503)->withBody($body);
    }

    return $next($request, $response);
});
```

### StackPHP

```php
use Janitor\Janitor;
use Janitor\ScheduledWatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Maintenance implements HttpKernelInterface
{
    private $app;
    private $janitor;

    public function __construct(HttpKernelInterface $app, Janitor $janitor)
    {
        $this->app = $app;
        $this->janitor = $janitor;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if ($this->janitor->inMaintenance() && !$this->janitor->isExcluded()) {
            $watcher = $this->janitor->getActiveWatcher();

            // Handle maintenance mode
            $message = 'Maintenance mode is active';
            if ($watcher instanceof ScheduledWatcher) {
                $message = 'Maintenance mode is active until' . $watcher->getEnd()->format('Y/m/d H:i:s');
            }

            return new Response($message, 503);
        }

        return $this->app->handle($request, $type, $catch);
    }
}

// Initialize Janitor with watchers and excluders
$janitor = new Janitor($watchers, $excluders);

$stack = (new \Stack\Builder())
    ->push('\Maintenance', $janitor);

$app = $stack->resolve($app);
```

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/janitor/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/janitor/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/janitor/blob/master/LICENSE) included with the source code for a copy of the license terms.
