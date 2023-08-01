<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Config;
use Phplc\Core\Container;
use Phplc\Core\Contracts\ErrorLog;
use Phplc\Core\Contracts\EventDispatcher;
use Phplc\Core\Contracts\LoggingProperty;
use Phplc\Core\Contracts\RetainProperty;
use Phplc\Core\System\ChangeTrackingStorage;
use Phplc\Core\System\CommandsServer\Server;
use Phplc\Core\System\DefaultErrorLog;
use Phplc\Core\System\DefaultLoggingPropertyService;
use Phplc\Core\System\DefaultRetainPropertyService;
use Phplc\Core\System\EventDispatcherDefault;
use Phplc\Core\System\EventProvider;

class InnerSystemBuilder
{
    public function build(Container $container): void
    {
        $build = [
            Config::class,
            ErrorLog::class => DefaultErrorLog::class,
            LoggingProperty::class => DefaultLoggingPropertyService::class,
            RetainProperty::class => DefaultRetainPropertyService::class,
        ];

        foreach ($build as $key => $value) {
            if (is_int($key)) {
                $this->buildIfNotExists($container, $value);
                continue;
            }
            $this->buildIfNotExists($container, $key, $value);
        }

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
     * @param class-string|null $concret 
     */
    private function buildIfNotExists(
        Container $container,
        string $abstract,
        string|null $concret = null,
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
