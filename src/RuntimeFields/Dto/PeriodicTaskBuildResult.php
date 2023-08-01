<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\Dto\PeriodicTaskFieldDto;
use Phplc\Core\RuntimeFields\ChangeTrackingField;

class PeriodicTaskBuildResult
{
    /** 
     * @param array<class-string<Task|Storage>, LoggingPropertyField[]> $loggingPropertyFields 
     * @param array<class-string<Task|Storage>, ChangeTrackingField[]> $changeTrackingField 
     */
    public function __construct(
        public PeriodicTaskFieldDto $periodicTaskField,
        public array $loggingPropertyFields,
        public array $changeTrackingField,
    ) {}
}
