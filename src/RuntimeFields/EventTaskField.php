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
}
