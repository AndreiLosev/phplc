<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Contracts\ErrorLog;
use Phplc\Core\Contracts\EventDispatcher;
use Phplc\Core\Contracts\RetainProperty;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\System\ChangeTrackingStorage;

class EventTaskField
{
    private readonly RetainPropertyHeandler $retainHeandler;
    private readonly ChangeTrackingFieldHeandler $changeTrackingHeandler;

    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param ChangeTrackingField[] $taskChangeTrackingPropertus
     * @param array<class-string<Storage>, RetainPropertyField[]> $storageRetainProerty
     * @param array<class-string<Storage>, ChangeTrackingField[]> $storageChangeTrackingProerty
     * @param \Closure(class-string<Storage>): Storage $makeStorage
     */
    public function __construct(
        private readonly Task $task,
        private readonly string $eventName, 
        private readonly ErrorLog $errLog,
        array $taskRetainPropertus,
        array $storageRetainProerty,
        array $taskChangeTrackingPropertus,
        array $storageChangeTrackingProerty,
        RetainProperty $retainService,
        \Closure $makeStorage,
        ChangeTrackingStorage $changeTrackingStorage,
        EventDispatcher $eventDispatcher,
    ) {
        $this->retainHeandler = new RetainPropertyHeandler(
            $taskRetainPropertus,
            $storageRetainProerty,
            $retainService,
            $makeStorage,
        );

        $this->changeTrackingHeandler = new ChangeTrackingFieldHeandler(
            $taskChangeTrackingPropertus,
            $storageChangeTrackingProerty,
            $changeTrackingStorage,
            $eventDispatcher,
            $makeStorage,
        );
    }

    public function match(string $eventName): bool
    {
        return $this->eventName === $eventName;
    }

    public function run(): void
    {
        try {
            $this->task->execute();
            $this->retainHeandler->saveProprty($this->task);
            $this->changeTrackingHeandler->heandler($this->task);
        } catch (\Throwable $e) {
            $this->errLog->log($e);
        }
    }

    public function init(): void
    {
        $this->retainHeandler->init($this->task);
    }

    /** 
     * @param class-string<Task> $taskName
     */
    public function taskIs(string $taskName): bool
    {
        return $this->task::class === $taskName;
    }
}
