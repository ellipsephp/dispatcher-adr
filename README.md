# ADR resolver

This package provides a factory decorator for objects implementing `Ellipse\DispatcherFactoryInterface` from [ellipse/dispatcher](https://github.com/ellipsephp/dispatcher) package. It allows to produce instances of `Ellipse\Dispatcher` using [ADR definitions](#adr-definitions).

**Require** php >= 7.0

**Installation** `composer require ellipse/dispatcher-adr`

**Run tests** `./vendor/bin/kahlan`

- [Create a dispatcher factory resolving ADR definitions](#create-a-dispatcher-factory-resolving-adr-definitions)
- [ADR definitions](#adr-definitions)

## Create a dispatcher factory resolving ADR definitions

This package provides an `Ellipse\Dispatcher\ActionResolver` class implementing `Ellipse\DispatcherFactoryInterface` which allows to decorate any other object implementing this interface.

It takes a container implementing `Psr\Container\ContainerInterface` as first parameter and the factory to decorate as second parameter.

Once decorated, the resulting dispatcher factory can be used to produce instances of `Ellipse\Dispatcher` by resolving [ADR definitions](#adr-definitions) as `Ellipse\Handlers\ActionRequestHandler` instances from the [ellipse/handlers-adr](https://github.com/ellipsephp/handlers-adr) package.

```php
<?php

namespace App;

use SomePsr11Container;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\ActionResolver;

// Get some Psr-11 container.
$container = new SomePsr11Container;

// Decorate a DispatcherFactoryInterface implementation with an ActionResolver.
$factory = new ActionResolver($container, new DispatcherFactory);
```

## ADR definitions

An instance of `ActionRequestHandler` needs a container entry id of an object implementing `Ellipse\ADR\DomainInterface`, a container entry id of an object implementing `Ellipse\Handlers\ResponderInterface` and an optional array of default input. An ADR definition defines which *Domain* class, *Responder* class and default input should be used by the `ActionRequestHandler`. It can take two forms:

- A string representing the fully qualified name of a *Domain* class
- An array with at least one string as first element representing the fully qualified name of a *Domain* class. The fully qualified name of a *Responder* class and a default input array can be specified as optional second and third elements

For example `SomeDomain::class`, `[SomeDomain::class]`, `[SomeDomain::class, SomeResponder::class]` and `[SomeDomain::class, SomeResponder::class, ['default' => 'value']]` are valid ADR definitions, assuming `SomeDomain` class implements `DomainInterface` and `SomeResponder` implements `ResponderInterface`.

When no *Responder* class name is specified (`SomeDomain::class` and `[SomeDomain::class]`), the container entry id used to retrieve the *Responder* instance defaults to `Ellipse\Handlers\ResponderInterface`. It allows to use a default *Responder* class when none is defined.

`ActionRequestHandler` logic is described on the [ellipse/handlers-adr](https://github.com/ellipsephp/handlers-adr#request-handler-using-adr-pattern) documentation page.

```php
<?php

namespace App;

use SomePsr11Container;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\ActionResolver;

// Get some Psr-11 container.
$container = new SomePsr11Container;

// Decorate a DispatcherFactoryInterface implementation with an ActionResolver.
$factory = new ActionResolver($container, new DispatcherFactory);

// Dispatchers using ADR definitions as Psr-15 request handler can now be created.
$dispatcher1 = $factory(SomeDomain::class, [new SomeMiddleware]);
$dispatcher2 = $factory([SomeDomain::class], [new SomeMiddleware]);
$dispatcher3 = $factory([SomeDomain::class, SomeResponder::class], [new SomeMiddleware]);
$dispatcher4 = $factory([SomeDomain::class, SomeResponder::class, ['default' => 'value']], [new SomeMiddleware]);
```
