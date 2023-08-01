<?php

namespace Phplc\Core;

use Amp\CancelledException;
use Amp\DeferredCancellation;
use Amp\Future;
use Phplc\Core\Contracts\ErrorLog;
use Phplc\Core\RuntimeFields\EventTaskFieldsCollection;
use Phplc\Core\RuntimeFields\LoggingPropertyFieldsCollection;
use Phplc\Core\RuntimeFields\PeriodicTaskFieldsCollection;
use Phplc\Core\System\ChangeTrackingStorage;
use Phplc\Core\System\CommandsServer\Server;
use Phplc\Core\System\EventProvider;
use Phplc\Core\RuntimeFields\InnerSystemBuilder;
use Phplc\Core\System\InnerSysteEvents;
use function Amp\async;
use Illuminate\Container\Container as IlluminateContainer;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\TaskFieldsFactory;
use Phplc\Core\Contracts\Storage;

class Runtime
{
    private InnerSystemBuilder $innerSystemBuilder;

    private TaskFieldsFactory $taskFieldsFactory;

    private Container $container;

    private DeferredCancellation $cancellationToken;

    /** 
     * @param class-string<Task>[] $tasks
     */
    public function __construct(
        private array $tasks,
        IlluminateContainer $container,
    ) {
        $this->container = new Container($container);
        $this->taskFieldsFactory = new TaskFieldsFactory();
        $this->innerSystemBuilder = new InnerSystemBuilder();
        $this->cancellationToken = new DeferredCancellation();
    }

    public function build(): void
    {
        $this->innerSystemBuilder->build($this->container);
        $this->loadAllUsedClasses();
        $this->configurateStorageAsSinglton();
        $this->configurateTasksAsSinglton();

        $this->setTaskFields();
    }

    public function run(): void
    {
        $commandServer = async(
            $this->container->make(Server::class)->lisnet(...),
            $this->cancellationToken->getCancellation(),
        );

        $changeTracking = async(
            $this->container->make(ChangeTrackingStorage::class)->run(...),
        );

        $logging = async(
            $this->container->make(LoggingPropertyFieldsCollection::class)->run(...),
            $this->cancellationToken->getCancellation(),
        );

        $periodiTasksFuture = async(
            $this->container->make(PeriodicTaskFieldsCollection::class)->run(...)
        );
        $eventTasksFuture = async(
            $this->container->make(EventProvider::class)->run(...),
            $this->container->make(EventTaskFieldsCollection::class),
            $this->innerEventExecutor(...)
        );

        [$errors] = Future\awaitAll([
            $periodiTasksFuture,
            $eventTasksFuture,
            $commandServer,
            $logging,
            $changeTracking,
        ]);

        foreach ($errors as $e) {
            if ($e instanceof CancelledException) {
                continue;
            }
            $this->container->make(ErrorLog::class)->log($e, true);
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
        $periodiTasks = [];
        $eventTasks = [];
        $loggingFields = [];
        $changeTrackingField = [];
        foreach ($this->tasks as $taskName) {
            $taskInstans = $this->container->make($taskName);
            $buildResult = $this->taskFieldsFactory->build($taskInstans);

            if (!empty($buildResult->periodicTask)) {
                $periodiTasks[] = $buildResult->periodicTask;
            }

            if (!empty($buildResult->eventTask)) {
                $eventTasks[] = $buildResult->eventTask;
            }

            foreach ($buildResult->loggingPropertyFields as $key => $fild) {
                if (count($fild) === 0) {
                    continue;
                }
                $loggingFields[$key] = $fild;
            }

            foreach ($buildResult->changeTrackingField as $key => $fild) {
                if (count($fild) === 0) {
                    continue;
                }
                $changeTrackingField[$key] = $fild;
            }
        }
        $this->container->singleton(
            PeriodicTaskFieldsCollection::class,
            function() use($periodiTasks) {
                $collection = new PeriodicTaskFieldsCollection(
                    $this->container,
                    $periodiTasks,
                );
                $collection->build();

                return $collection;
            },
        );

        $this->container->singleton(
            EventTaskFieldsCollection::class,
            function() use($eventTasks) {
                $collection = new EventTaskFieldsCollection(
                    $this->container,
                    $eventTasks,
                );
                $collection->build();

                return $collection;
            },
        );

        $this->container->singleton(
            LoggingPropertyFieldsCollection::class,
            fn() => new LoggingPropertyFieldsCollection(
                $this->container,
                $loggingFields,
            ),
        );

        $this->container->singleton(
            ChangeTrackingStorage::class,
            function() use($changeTrackingField) {
                $collection = new ChangeTrackingStorage(
                    $this->container,
                    $changeTrackingField,
                );
                $collection->build();
                return $collection;
            }
        );

    }

    private function innerEventExecutor(string $event): void
    {
        if ($event === InnerSysteEvents::CANSEL_EVENT) {
            $this->stopAll();
        }
    }

    private  function stopAll(): void
    {
        $this->container->make(EventProvider::class)->cancel();
        $this->container->make(PeriodicTaskFieldsCollection::class)->cancel();
        $this->container->make(ChangeTrackingStorage::class)->cancel();
        $this->cancellationToken->cancel();
    }
}
