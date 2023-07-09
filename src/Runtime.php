<?php

namespace Phplc\Lib;

use Illuminate\Container\Container;
use Phplc\Core\Сontracts\Storage;

class Runtime
{
    /** 
     * @param string[] $tasks
     */
    public function __construct(
        private Container $container,
        private array $tasks,
    ) {}

    public function build(): void
    {
        $this->loadAllUsedClasses();
        $this->configurateStorageAsSinglton();
    }

    private function loadAllUsedClasses(): void
    {
        foreach ($this->tasks as $task) {
            $this->container->make($task);
        }
    }

    private function configurateStorageAsSinglton(): void
    {
        $storages = array_filter(
            get_declared_classes(),
            fn(string $name) => in_array(Storage::class, class_implements($name)),
        );

        foreach ($storages as $name) {
            $this->container->singleton($name);
        }
    }


}
