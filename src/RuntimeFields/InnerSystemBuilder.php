<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Config;
use Phplc\Core\Container;
use Phplc\Core\Contracts\EventDispatcher;
use Phplc\Core\Contracts\LoggingProperty;
use Phplc\Core\Contracts\RetainProperty;
use Phplc\Core\System\ChangeTrackingStorage;
use Phplc\Core\System\CommandsServer\Server;
use Phplc\Core\System\DefaultLoggingPropertyService;
use Phplc\Core\System\DefaultRetainPropertyService;
use Phplc\Core\System\EventDispatcherDefault;
use Phplc\Core\System\EventProvider;

class InnerSystemBuilder
{
    public function build(Container $container): void
    {
        $this->buildIfNotExists($container, Config::class, Config::class);
        $this->buildIfNotExists(
            $container,
            LoggingProperty::class,
            DefaultLoggingPropertyService::class
        );
        $this->buildIfNotExists(
            $container,
            RetainProperty::class,
            DefaultRetainPropertyService::class,
        );
        $this->buildIfNotExists(
            $container,
            ChangeTrackingStorage::class,
            ChangeTrackingStorage::class,
        );

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

    /** 
     * @param interface-string $abstract
     * @param class-string $concret 
     */
    private function buildIfNotExists(
        Container $container,
        string $abstract,
        string $concret,
    ): void {
        if (!$container->isSinglton($abstract)) {
            $container->singleton($abstract, $concret);
        }

        $instants = $container->make($abstract);
        if (method_exists($instants, 'build')) {
            $instants->build();
        }
    }
}
