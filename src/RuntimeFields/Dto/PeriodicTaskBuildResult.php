<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\PeriodicTaskField;

class PeriodicTaskBuildResult
{
    /** 
     * @param array<class-string<Task|Storage>, LoggingPropertyField[]> $loggingPropertyFields 
     */
    public function __construct(
        public PeriodicTaskField $periodicTaskField,
        public array $loggingPropertyFields,
    ) {}
}
