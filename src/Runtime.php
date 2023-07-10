<?php

namespace Phplc\Core;

use Illuminate\Container\Container;
use Phplc\Core\Contracts\Task;
use phpDocumentor\Reflection\Types\ClassString;
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

    /** 
     * @param array<ClassString<Task>> $tasks
     */
    public function __construct(
        private Container $container,
        private array $tasks,
    ) {
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
        foreach ($this->tasks as $task) {
            $this->container->make($task);
        }
    }

    private function configurateStorageAsSinglton(): void {
    
        $storages = array_filter(
            get_declared_classes(),
            fn(string $name) => in_array(Storage::class, class_implements($name)),
        );

        foreach ($storages as $name) {
            $this->container->singleton($name);
        }
    }

    private function configurateTasksAsSinglton(): void {
        foreach ($this->tasks as $name) {
            $this->container->singleton($name);
        }
    }

    private function setTaskFields(): void
    {
        foreach ($this->tasks as $taskName) {
            /** @var Task */
            $taskInstans = $this->container->make($taskName);
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
