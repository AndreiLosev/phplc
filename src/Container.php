<?php

namespace Phplc\Core;

use Illuminate\Container\Container as IlluminateContainer;
use Phplc\Core\Contracts\Task;

class Container
{
    public function __construct(
        private IlluminateContainer $container, 
    ) {}

    /**
     * @param class-string<Task> $abstract 
     * @return Task
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function makeTask($abstract)
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
        $this->container->bind($abstract, $concrete, true);
    }
}
