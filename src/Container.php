<?php

namespace Phplc\Core;

use Illuminate\Container\Container as IlluminateContainer;
use Phplc\Core\Contracts\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Phplc\Core\RuntimeFields\EventProvider;

class Container
{
    public function __construct(
        private IlluminateContainer $container, 
    ) {}

    /**
     * @param class-string<Task> $abstract 
     * @throws BindingResolutionException
     */
    public function makeTask($abstract): Task
    {
        /** @var Task */
        $instans = $this->container->make($abstract);
        return $instans;
    }

    /**
     * @param  class-string  $abstract
     * @param  \Closure|string|null  $concrete
     */
    public function singleton($abstract, $concrete = null): void
    {
        $this->container->singleton($abstract, $concrete);
    }

    /**
     * @param class-string<EventProvider> $abstract 
     * @throws BindingResolutionException
     */
    public function makeEventProvider($abstract): EventProvider
    {
        /** @var EventProvider */
        $instans = $this->container->make($abstract);
        return $instans;    
    }

    /** 
     * @template T 
     * @param class-string<T> $abstract 
     * @throws BindingResolutionException
     * @return T
     */
    public function makeAny($abstract): mixed
    {
        return $this->container->make($abstract);
    }

    /**
     * @param  class-string $abstract
     * @throws \TypeError
     */
    public function bind(
        string $abstract,
        \Closure|string|null $concrete = null,
        bool $shared = false
    ): void {
        $this->container->bind($abstract, $concrete = null, $shared = false);
    }
}
