A set of useful extra classes for Kohana - including wrappers that make it easier to inject
or fake parts of the core Kohana underlying behaviour.

[![Build Status](https://travis-ci.org/ingenerator/kohana-extras.svg?branch=1.x)](https://travis-ci.org/ingenerator/kohana-extras)


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

# Contributing

Contributions are welcome but please contact us before you start work on anything to check your
plans line up with our thinking and future roadmap. 

# Contributors

This package has been sponsored by [inGenerator Ltd](http://www.ingenerator.com)

* Andrew Coulton [acoulton](https://github.com/acoulton) - Lead developer

# Licence

Licensed under the [BSD-3-Clause Licence](LICENSE)
