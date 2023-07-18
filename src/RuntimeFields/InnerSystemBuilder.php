<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Container;
use Phplc\Core\Contracts\EventDispatcher;
use Phplc\Core\System\CommandsServer\Server;
use Phplc\Core\System\EventDispatcherDefault;
use Phplc\Core\System\EventProvider;

class InnerSystemBuilder
{
    public function build(Container $container): void
    {
        $this->buildEventProvider($container);
        $this->buildCommandServer($container);
    }

    private function buildEventProvider(
        Container $container,
    ): void
    {
        $container->singleton(EventProvider::class);

        $container->singleton(
            EventDispatcher::class,
            fn() => new EventDispatcherDefault(
                $container->make(EventProvider::class)->dispatchEvent(...),
            ),
        );
    }

    private function buildCommandServer(Container $container): void
    {
        $container->singleton(
            Server::class,
            fn() => new Server($container),
        );
    }
}
