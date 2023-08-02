A set of useful extra classes for Kohana - including wrappers that make it easier to inject
or fake parts of the core Kohana underlying behaviour.

# Installing kohana-extras

This isn't in packagist yet : you'll need to add our package repository to your composer.json:

```json
{
  "repositories": [
    {"type": "composer", "url": "https://php-packages.ingenerator.com"}
  ]
}
```

`$> composer require ingenerator/kohana-extras`

## Using the debug bar

This package provides a debug bar you can use in development to show logs / doctrine queries 
and similar.

To use it:

* `composer require maximebf/debugbar`
* include `DebugBarFactory::definitions()` in your dependency container
* initialise the debug bar in your bootstrap when appropriate:

```php
// Initialise the debug bar if in the development environment
if (\Kohana::$environment === Kohana::DEVELOPMENT) {
    $bar = $deps->get('debug_bar.bar');
    /** @var DebugBar $bar */
    $bar->initialise();
}
```

* render the bar in your template when appropriate:

```php
$debug_bar      = $dependencies->get('debug_bar.bar');
<?php if ($debug_bar->isActive()):?>
    <?=raw($debug_bar->render());?>
<?php endif ;?>
```

# Using the garbage collection endpoint

The package provides classes to give your app a shared HTTP endpoint to trigger garbage collection. Garbage collection
is often a fairly light-touch process, and in a containerised environment is ideal for running as a regular HTTP ping
within an existing appserver pool rather than the overhead of a dedicated background job / service.

By default the package provides a garbage collector for our MysqlSession database-backed session handler. This can be
disabled and/or you can easily register app-specific garbage collectors that trigger off the same HTTP ping.

To add this to your application:

```php
// routes.php
use Ingenerator\KohanaExtras\GarbageCollector\GarbageCollectionController;
use Ingenerator\KohanaExtras\Routing\HttpMethodRoute;
// register on a URL to suit your routing / path structure
HttpMethodRoute::createExplicit('_gc', '_internal/garbage_collect', GarbageCollectionController::class);
```

```php
// application/config/application.php
return [
    // This is used for basic authentication of the HTTP request. Generally, your garbage collectors should be written
    // to have limited side-effects / misuse potential. For example, always just delete records that are older than 
    // a fixed lifetime. This makes it unlikely that misuse of this token could cause any major implications for your
    // app - unauthorised requests will at worst clear a few records that would have gone soon anyway, and at best be a
    // no-op. There is a theoretical possibility of DDOS type attacks - but for most sane garbage collector
    // implementations it would be hard to do more damage than just hitting your site's public interfaces.
    'garbage_collection_token' => 'take-out-the-trash'
    // ... Your other app config ...
];
```

```php
// application/config/dependencies.php
use Ingenerator\KohanaExtras\DependencyFactory\GarbageCollectorFactory;
return [
  '_include' => [
     // See this method for information on registering your own custom collectors
     GarbageCollectorFactory::definitions(),
     /* your other includes and definitions */
  ]
];
```

You can then call it from anywhere e.g.

```shell
curl --fail -X POST -H "Authorization: Bearer take-out-the-trash" https://my.app/_internal/garbage_collect
```

# Contributing

Contributions are welcome but please contact us before you start work on anything to check your
plans line up with our thinking and future roadmap. 

# Contributors

This package has been sponsored by [inGenerator Ltd](http://www.ingenerator.com)

* Andrew Coulton [acoulton](https://github.com/acoulton) - Lead developer

# Licence

Licensed under the [BSD-3-Clause Licence](LICENSE)
