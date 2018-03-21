# ADR resolver

This package provides a factory decorator for objects implementing `Ellipse\DispatcherFactoryInterface` from [ellipse/dispatcher](https://github.com/ellipsephp/dispatcher) package.

The resulting factory uses a [Psr-11](http://www.php-fig.org/psr/psr-11/) container to produce instances of `Ellipse\Dispatcher` using [ADR definitions](#adr-definition) as [Psr-15](https://www.php-fig.org/psr/psr-15/) request handler.

**Require** php >= 7.0

**Installation** `composer require ellipse/dispatcher-adr`

**Run tests** `./vendor/bin/kahlan`
