<?php

namespace Phplc\Core;

use Amp\Future;
use Phplc\Core\System\EventProvider;
use Phplc\Core\RuntimeFields\InnerSystemBuilder;
use Phplc\Core\System\InnerSysteEvents;
use function Amp\async;
use function Amp\delay;
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

    private InnerSystemBuilder $innerSystemBuilder;

    /** 
     * @var array<string, LoggingPropertyField[]> 
     */
    private array $loggingFields;

    private TaskFieldsFactory $taskFieldsFactory;

    private Container $container;

    private EventProvider $eventProvider;

    /** 
     * @var string[] 
     */
    private array $events;

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
        $this->events = [];
        $this->eventProvider = new EventProvider();
        $this->innerSystemBuilder = new InnerSystemBuilder();
    }

    public function build(): void
    {
        $this->loadAllUsedClasses();
        $this->configurateStorageAsSinglton();
        $this->configurateTasksAsSinglton();
        $this->innerSystemBuilder->build(
            $this->container,
            $this->eventProvider,
        );

        $this->setTaskFields();
    }

    public function run(): void
    {
        $periodiTasksFuture = async($this->runPeriodicTasks(...));
        $eventTasksFuture = async($this->runEvemtTasks(...));

        Future\awaitAll([
            $periodiTasksFuture,
            $eventTasksFuture,
        ]);
    }

    private function runPeriodicTasks(): void
    {
        $fetures = [];
        for ($i = 0; $i < count($this->periodiTasks); $i++) {
            $fetures[] = async($this->periodiTasks[$i]->run(...));        
        }

       Future\awaitAll($fetures);
    }

    private function runEvemtTasks(): void
    {
        while ($event = $this->eventProvider->shift()) {
            if ($this->eventProvider->isRepeat($event)) {
                $this->next();
                continue;
            }

            if ($this->eventProvider->isInnerSystemEvent($event)) {
                $this->innerEventEcecuter($event);
                $this->next();
                continue;
            }

            for ($i = 0; $i < count($this->eventTasks); $i++) {
                if ($this->eventTasks[$i]->match($event)) {
                    $this->eventTasks[$i]->run();
                }
            }

            $this->next();
        }
    }

    private function loadAllUsedClasses(): void
    {
        foreach ($this->tasks as $taskName) {
            $this->container->make($taskName);
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
            $taskInstans = $this->container->make($taskName);
            $buildResult = $this->taskFieldsFactory->build($taskInstans);

            if (!empty($buildResult->periodicTask)) {
                $this->periodiTasks[] = $buildResult->periodicTask;
            }

            if (!empty($buildResult->eventTask)) {
                $this->eventTasks[] = $buildResult->eventTask;
            }

            foreach ($buildResult->loggingPropertyFields as $key => $fild) {
                if (count($fild) === 0) {
                    continue;
                }
                $this->loggingFields[$key] = $fild;
            }
        }
    }

    private function next(): void
    {
        delay(0.01);
    }

    private function innerEventEcecuter(string $event): void
    {
        if ($event === InnerSysteEvents::CANSEL_EVENT) {
            $this->stopAll();
        }
    }

    private  function stopAll(): void
    {
        $this->eventProvider->cancel();
        for ($i = 0; $i < count($this->periodiTasks); $i++) { 
            $this->periodiTasks[$i]->cancel();
        }
    }
}
