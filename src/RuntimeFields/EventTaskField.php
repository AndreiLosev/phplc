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
        private Task $task,
        private string $eventName, 
        private array $taskRetainPropertus,
        private array $storageRetainProerty,
    ) {}
}
