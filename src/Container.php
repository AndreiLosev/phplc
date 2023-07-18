<?php

namespace Phplc\Core;

use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Contracts\Container\BindingResolutionException;

class Container
{
    public function __construct(
        private IlluminateContainer $container, 
    ) {}

    /**
     * @param  class-string  $abstract
     * @param  \Closure|string|null  $concrete
     */
    public function singleton($abstract, $concrete = null): void
    {
        $this->container->singleton($abstract, $concrete);
    }

    /** 
     * @template T 
     * @param class-string<T> $abstract 
     * @throws BindingResolutionException
     * @return T
     */
    public function make($abstract): mixed
    {
        /** @var T */
        $instans = $this->container->make($abstract);
        return $instans;
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
