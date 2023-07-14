<?php

namespace Phplc\Core;

use Illuminate\Container\Container as IlluminateContainer;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\EventTaskField;
use Phplc\Core\RuntimeFields\PeriodicTaskField;
use Illuminate\Contracts\Container\BindingResolutionException;

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
        $this->container->bind($abstract, $concrete, true);
    }


}
