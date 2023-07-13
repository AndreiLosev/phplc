<?php

namespace Phplc\Core;

use Illuminate\Container\Container as IlluminateContainer;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\EventTaskField;
use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\PeriodicTaskField;
use Phplc\Core\RuntimeFields\TaskFieldsFactory;
use Phplc\Core\Contracts\Storage;

class Runtime
{
    /** 
     * @var PeriodicTaskField[] 
     */
    private array $periodiTasks;

    /** 
     * @var EventTaskField[] 
     */
    private array $eventTasks;

    /** 
     * @var array<string, LoggingPropertyField[]> 
     */
    private array $loggingFields;

    private TaskFieldsFactory $taskFieldsFactory;

    private Container $container;

    /** 
     * @param class-string<Task>[] $tasks
     */
    public function __construct(
        private array $tasks,
        IlluminateContainer $container,
    ) {
        $this->container = new Container($container);
        $this->periodiTasks = [];
        $this->eventTasks = [];
        $this->loggingFields = [];
        $this->taskFieldsFactory = new TaskFieldsFactory();
    }

    public function build(): void
    {
        $this->loadAllUsedClasses();
        $this->configurateStorageAsSinglton();
        $this->configurateTasksAsSinglton();
        $this->setTaskFields();
    }

    private function loadAllUsedClasses(): void
    {
        foreach ($this->tasks as $taskName) {
            $this->container->makeTask($taskName);
        }
    }

    private function configurateStorageAsSinglton(): void
    {
        /** @var class-string<Storage>[] */
        $storages = array_filter(
            get_declared_classes(),
            fn(string $name) => in_array(Storage::class, class_implements($name)),
        );

        foreach ($storages as $name) {
            $this->container->singleton($name);
        }
    }

    private function configurateTasksAsSinglton(): void {
        foreach ($this->tasks as $taskName) {
            $this->container->singleton($taskName);
        }
    }

    private function setTaskFields(): void
    {
        foreach ($this->tasks as $taskName) {
            $taskInstans = $this->container->makeTask($taskName);
            $buildResult = $this->taskFieldsFactory->build($taskInstans);

            if (!empty($buildResult->periodicTask)) {
                $this->periodiTasks[] = $buildResult->periodicTask;
            }

            if (!empty($buildResult->eventTask)) {
                $this->eventTasks[] = $buildResult->eventTask;
            }

            foreach ($buildResult->loggingPropertyFields as $key => $fild) {
                $this->loggingFields[$key] = $fild;
            }
        }
    }
}
