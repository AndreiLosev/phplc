<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\Dto\PeriodicTaskFieldDto;

class PeriodicTaskBuildResult
{
    /** 
     * @param array<class-string<Task|Storage>, LoggingPropertyField[]> $loggingPropertyFields 
     */
    public function __construct(
        public PeriodicTaskFieldDto $periodicTaskField,
        public array $loggingPropertyFields,
    ) {}
}
