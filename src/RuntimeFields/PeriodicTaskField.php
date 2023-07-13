<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Contracts\Task;

class PeriodicTaskField
{
    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param array<class-string, RetainPropertyField[]> $storageRetainProerty
     */
    public function __construct(
        protected Task $task,
        protected int $preiodMilis,
        protected array $taskRetainPropertus,
        protected array $storageRetainProerty,
        protected int $startTime = 0,
    ) {}
}
