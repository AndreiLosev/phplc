<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;

class EventTaskField
{
    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param ChangeTrackingField[] $taskChangeTrackingPropertus
     * @param array<class-string<Storage>, RetainPropertyField[]> $storageRetainProerty
     * @param array<class-string<Storage>, ChangeTrackingField[]> $storageChangeTrackingProerty
     */
    public function __construct(
        private Task $task,
        private string $eventName, 
        private array $taskRetainPropertus,
        private array $storageRetainProerty,
        private array $taskChangeTrackingPropertus,
        private array $storageChangeTrackingProerty,
    ) {}

    public function match(string $eventName): bool
    {
        return $this->eventName === $eventName;
    }

    public function run(): void
    {
        try {
            $this->task->execute();
            // TODO retain property
        } catch (\Throwable $th) {
            //TODO;
        }
    }

    /** 
     * @param class-string<Task> $taskName
     */
    public function taskIs(string $taskName): bool
    {
        return $this->task::class === $taskName;
    }
}
