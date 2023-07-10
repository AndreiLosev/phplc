<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Сontracts\Task;

class PeriodicTaskField
{
    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param array<string, RetainPropertyField> $storageRetainProerty
     */
    public function __construct(
        private Task $task,
        private int $preiodMilis,
        private array $taskRetainPropertus,
        private array $storageRetainProerty,
        private int $startTime = 0,
    ) {}
}
