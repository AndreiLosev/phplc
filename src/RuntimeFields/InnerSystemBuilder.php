<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Config;
use Phplc\Core\Container;
use Phplc\Core\Contracts\EventDispatcher;
use Phplc\Core\System\CommandsServer\Server;
use Phplc\Core\System\EventDispatcherDefault;
use Phplc\Core\System\EventProvider;

class InnerSystemBuilder
{
    public function build(Container $container): void
    {
        $this->buildConfig($container);
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
        $config = $container->make(Config::class);
        $container->singleton(
            Server::class,
            fn() => new Server($container, $config->commandSeverAddr),
        );
    }

    private function buildConfig(Container $container): void
    {
        if (!$container->isSinglton(Config::class)) {
            $container->singleton(Config::class);
        }
        $container->make(Config::class)->build();
    }
}
