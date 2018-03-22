<?php

use function Eloquent\Phony\Kahlan\mock;
use function Eloquent\Phony\Kahlan\onStatic;

use Psr\Container\ContainerInterface;

use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\Dispatcher\ActionResolver;
use Ellipse\ADR\DomainInterface;
use Ellipse\Handlers\ResponderInterface;
use Ellipse\Handlers\ActionRequestHandler;

describe('ActionResolver', function () {

    beforeEach(function () {

        $this->container = mock(ContainerInterface::class)->get();
        $this->delegate = mock(DispatcherFactoryInterface::class);

        $this->resolver = new ActionResolver($this->container, $this->delegate->get());

    });

    it('should implement DispatcherFactoryInterface', function () {

        expect($this->resolver)->toBeAnInstanceOf(DispatcherFactoryInterface::class);

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->domain = onStatic(mock(DomainInterface::class))->className();
            $this->responder = onStatic(mock(ResponderInterface::class))->className();

            $this->dispatcher = mock(Dispatcher::class)->get();

        });

        context('when the given request handler is not an ADR action definition', function () {

            it('should proxy the delegate with the given request handler', function () {

                $test = function ($handler) {

                    $this->delegate->__invoke->with($handler, '~')->returns($this->dispatcher);

                    $test = ($this->resolver)($handler, []);

                    expect($test)->toBe($this->dispatcher);

                };

                $test('Domain'); // Not a domain class name
                $test([]); // Empty array
                $test(['Domain']); // Array without a domain class name as first element
                $test([$this->domain, 'Responder']); // Array without a responder class name as second element
                $test([$this->domain, $this->responder, 'input']); // Array without an array as third element

            });

        });

        context('when the given request handler is an ADR action definition', function () {

            context('when the ADR action definition is a domain class name', function () {

                it('should use a new ActionRequestHandler using the defined domain, default responder and an empty array of default input', function () {

                    $handler = new ActionRequestHandler($this->container, $this->domain, ResponderInterface::class, []);

                    $this->delegate->__invoke->with($handler, '~')->returns($this->dispatcher);

                    $test = ($this->resolver)($this->domain);

                    expect($test)->toBe($this->dispatcher);

                });

            });

            context('when the ADR action definition is an array', function () {

                context('when no responder class name is defined', function () {

                    it('should use a new ActionRequestHandler using the defined domain, default responder and an empty array of default input', function () {

                        $handler = new ActionRequestHandler($this->container, $this->domain, ResponderInterface::class, []);

                        $this->delegate->__invoke->with($handler, '~')->returns($this->dispatcher);

                        $test = ($this->resolver)([$this->domain]);

                        expect($test)->toBe($this->dispatcher);

                    });

                });

                context('when a responder class name is defined', function () {

                    context('when no default input is defined', function () {

                        it('should use a new ActionRequestHandler using the defined domain and responder and an empty array of default input', function () {

                            $handler = new ActionRequestHandler($this->container, $this->domain, $this->responder, []);

                            $this->delegate->__invoke->with($handler, '~')->returns($this->dispatcher);

                            $test = ($this->resolver)([$this->domain, $this->responder]);

                            expect($test)->toBe($this->dispatcher);

                        });

                    });

                    context('when a default input is defined', function () {

                        it('should use a new ActionRequestHandler using the defined domain, responder and default input', function () {

                            $handler = new ActionRequestHandler($this->container, $this->domain, $this->responder, ['input']);

                            $this->delegate->__invoke->with($handler, '~')->returns($this->dispatcher);

                            $test = ($this->resolver)([$this->domain, $this->responder, ['input']]);

                            expect($test)->toBe($this->dispatcher);

                        });

                    });

                });

            });

        });

        context('when no middleware queue is given', function () {

            it('should proxy the delegate with an empty array', function () {

                $this->delegate->__invoke->with('~', [])->returns($this->dispatcher);

                $test = ($this->resolver)('handler');

                expect($test)->toBe($this->dispatcher);

            });

        });

        context('when an middleware queue is given', function () {

            it('should proxy the delegate with the given middleware queue', function () {

                $this->delegate->__invoke->with('~', ['middleware'])->returns($this->dispatcher);

                $test = ($this->resolver)('handler', ['middleware']);

                expect($test)->toBe($this->dispatcher);

            });

        });

    });

});
