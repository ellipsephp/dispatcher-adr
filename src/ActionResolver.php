<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Container\ContainerInterface;

use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\ADR\DomainInterface;
use Ellipse\Handlers\ResponderInterface;
use Ellipse\Handlers\ActionRequestHandler;

class ActionResolver implements DispatcherFactoryInterface
{
    /**
     * The container.
     *
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * The delegate.
     *
     * @var \Ellipse\DispatcherFactoryInterface
     */
    private $delegate;

    /**
     * Set up a controller resolver with the given container and delegate.
     *
     * @param \Psr\Container\ContainerInterface     $container
     * @param \Ellipse\DispatcherFactoryInterface   $delegate
     */
    public function __construct(ContainerInterface $container, DispatcherFactoryInterface $delegate)
    {
        $this->container = $container;
        $this->delegate = $delegate;
    }

    /**
     * Proxy the delegate by wrapping ADR action definitions into action request
     * handlers.
     *
     * @param mixed $handler
     * @param array $middleware
     * @return \Ellipse\Dispatcher
     */
    public function __invoke($handler, array $middleware = []): Dispatcher
    {
        $definition = is_string($handler) ? [$handler] : $handler;

        // when the handler is an array.
        if (is_array($definition)) {

            // set values for domain, responder and default input.
            $domain = array_shift($definition) ?? null;
            $responder = array_shift($definition) ?? ResponderInterface::class;
            $input = array_shift($definition) ?? [];

            // check if the domain, responder and default input are valid.
            $valid = $this->isValidDomain($domain)
                && $this->isValidResponder($responder)
                && $this->isValidDefaultInput($input);

            // set handler as action request handler when the definition is valid.
            if ($valid) {

                $handler = new ActionRequestHandler($this->container, $domain, $responder, $input);

            }

        }

        return ($this->delegate)($handler, $middleware);
    }

    /**
     * Retrun whether the given value is a valid domain id.
     *
     * @param mixed $value
     * @return bool
     */
    private function isValidDomain($value): bool
    {
        return is_string($value)
            && is_subclass_of($value, DomainInterface::class, true);
    }

    /**
     * Retrun whether the given value is a valid responder id.
     *
     * @param mixed $value
     * @return bool
     */
    private function isValidResponder($value): bool
    {
        return is_string($value) && (
            $value == ResponderInterface::class
            ||
            is_subclass_of($value, ResponderInterface::class, true)
        );
    }

    /**
     * Retrun whether the given value is a valid default input.
     *
     * @param mixed $value
     * @return bool
     */
    private function isValidDefaultInput($value): bool
    {
        return is_array($value);
    }
}
