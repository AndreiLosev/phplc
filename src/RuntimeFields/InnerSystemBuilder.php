<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Container;
use Phplc\Core\Contracts\EventDispatcher;
use Phplc\Core\System\CommandsServer\Server;
use Phplc\Core\System\EventDispatcherDefault;
use Phplc\Core\System\EventProvider;

class InnerSystemBuilder
{
    public function build(
        Container $container,
        EventProvider $eventProvider,
    ): void
    {
        $this->buildEventProvider($container, $eventProvider);
    }

    private function buildEventProvider(
        Container $container,
        EventProvider $eventProvider,
    ): void
    {
        $container->singleton(
            EventProvider::class,
            fn() => $eventProvider,
        );

        $container->singleton(
            EventDispatcher::class,
            fn() => new EventDispatcherDefault(
                $eventProvider->dispatchEvent(...),
            ),
        );
    }

    private function buildCommandServer(
        Container $container,
    ): void {
        $container->singleton(Server::class);
    }
}
