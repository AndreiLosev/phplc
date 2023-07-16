<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Contracts\Task;

class EventTaskField
{
    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param array<string, RetainPropertyField[]> $storageRetainProerty
     */
    public function __construct(
        protected Task $task,
        protected string $eventName, 
        protected array $taskRetainPropertus,
        protected array $storageRetainProerty,
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
}
