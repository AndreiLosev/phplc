<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\Dto\EventTaskFieldDto;
use Phplc\Core\RuntimeFields\LoggingPropertyField;

class EventTaskBuildResult 
{
    /** 
     * @param array<class-string<Storage|Task>, LoggingPropertyField[]> $loggingPropertyFields 
     */
    public function __construct(
        public EventTaskFieldDto $eventTaskField,
        public array $loggingPropertyFields,
    ) {}
}
