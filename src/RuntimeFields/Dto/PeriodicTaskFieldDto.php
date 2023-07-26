<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\ChangeTrackingField;
use Phplc\Core\RuntimeFields\RetainPropertyField;

class PeriodicTaskFieldDto
{
    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param ChangeTrackingField[] $taskChangeTrackingPropertus
     * @param array<class-string<Storage>, RetainPropertyField[]> $storageRetainProerty
     * @param array<class-string<Storage>, ChangeTrackingField[]> $storageChangeTrackingProerty
     */
    public function __construct(
        public Task $task,
        public float $periodMilis,
        public array $taskRetainPropertus,
        public array $storageRetainProerty,
        public array $taskChangeTrackingPropertus,
        public array $storageChangeTrackingProerty,
    ) {}
}
