<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\RetainPropertyField;

class EventTaskFieldDto
{
    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param array<class-string<Storage>, RetainPropertyField[]> $storageRetainProerty
     */
    public function __construct(
        public Task $task,
        public string $eventName, 
        public array $taskRetainPropertus,
        public array $storageRetainProerty,
    ) {}
}
