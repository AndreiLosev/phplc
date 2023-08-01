<?php

namespace Phplc\Core\System;

use Phplc\Core\Config;
use Phplc\Core\Container;
use Phplc\Core\Contracts\EventDispatcher;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\Helpers;
use Phplc\Core\RuntimeFields\ChangeTrackingField;

class ChangeTrackingStorage
{
    /** 
     * @var array<string, scalar> 
     */
    private array $values = [];

    private readonly int $decimalPlaces;

    private bool $cancelToken = false;

    private readonly EventDispatcher $eventDispatcher;

    /** 
     * @param array<class-string<Task|Storage>, ChangeTrackingField[]> $collection 
     */
    public function __construct(
        private readonly Container $container,
        private readonly array $collection,
    ) {
        $this->decimalPlaces = $container->make(Config::class)
            ->decimalPlacesForChangeTracking;
        $this->eventDispatcher = $container->make(EventDispatcher::class);

    }

    public function build(): void
    {
        $fn = function(ChangeTrackingField $f, Task|Storage $i): void {
            /** @var scalar $value */
            [$key, $value] = $f->getKeyValue($i);
            $this->values[$key] = $value;
        };

        $this->enumeration($fn);
    }

    public function run(): void
    {
        while (!$this->cancelToken) {
            $this->enumeration($this->runOne(...));
            Helpers::next();
        }
    }

    public function cancel(): void
    {
        $this->cancelToken = true;
    }

    private function runOne(ChangeTrackingField $f, Task|Storage $i): void
    {
        /** @var scalar $value */
        [$key, $value] = $f->getKeyValue($i);

        if (!$this->valueIsChanged($key, $value)) {
            return;
        }

        $this->eventDispatcher->dispatch($f->event);
        Helpers::next();
    }

    /** 
     * @param \Closure(ChangeTrackingField, Task|Storage): void $fn 
     */
    private function enumeration(\Closure $fn): void
    {
        foreach ($this->collection as $class => $fields) {
            $instants = $this->container->make($class);
            for ($i = 0; $i < count($fields); $i ++) { 
                $fn($fields[$i], $instants);
            }
        }
    }

    private function valueIsChanged(string $name, bool|int|float|string $value): bool
    {
        $newValue = is_float($value) ? round($value, $this->decimalPlaces) : $value;

        if (!isset($this->values[$name])) {
            $this->values[$name] = $newValue;
            return true;
        }

        if ($newValue === $this->values[$name]) {
            return false;
        }

        $this->values[$name] = $newValue;
        return true;
    }
}
