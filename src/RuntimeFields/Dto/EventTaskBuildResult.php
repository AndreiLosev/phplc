<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\EventTaskField;
use Phplc\Core\RuntimeFields\LoggingPropertyField;

class EventTaskBuildResult 
{
    /** 
     * @param array<class-string<Storage|Task>, LoggingPropertyField[]> $loggingPropertyFields 
     */
    public function __construct(
        public EventTaskField $eventTaskField,
        public array $loggingPropertyFields,
    ) {}
}
