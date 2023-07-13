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
     * @param class-string<PeriodicTaskField> $abstract 
     * @param mixed[] $params
     *
     * @throws BindingResolutionException
     */
    public function makePeriodictTaskField(
        string $abstract,
        array $params,
    ): PeriodicTaskField {
        $instans = $this->container->make($abstract, $params);

        if (!($instans instanceof PeriodicTaskField)) {
            throw new BindingResolutionException;
        }
        return $instans;
    }

    /**
     * @param class-string<EventTaskField> $abstract 
     * @param mixed[] $params
     *
     * @throws BindingResolutionException
     */
    public function makeEventTaskField(
        string $abstract,
        array $params,
    ): EventTaskField {
        $instans = $this->container->make($abstract, $params);

        if (!($instans instanceof EventTaskField)) {
            throw new BindingResolutionException;
        }
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
