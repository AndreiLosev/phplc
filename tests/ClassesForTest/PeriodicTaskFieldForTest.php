<?php

namespace tests\ClassesForTest;

use Phplc\Core\RuntimeFields\PeriodicTaskField;

class PeriodicTaskFieldForTest extends PeriodicTaskField
{
    /** 
     * @return array<string, mixed> 
     */
    public  function getAll(): array
    {
        return [
            'task' => $this->task,
            'preiodMilis' => $this->preiodMilis,
            'taskRetainPropertus' => $this->taskRetainPropertus,
            'storageRetainProerty' => $this->storageRetainProerty,
            'startTime' => $this->startTime,
        ];
    }
}
