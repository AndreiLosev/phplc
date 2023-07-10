<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\RuntimeFields\EventTaskField;
use Phplc\Core\RuntimeFields\LoggingPropertyField;

class EventTaskBuildResult 
{
    /** 
     * @param array<string, LoggingPropertyField[]> $loggingPropertyFields 
     */
    public function __construct(
        public EventTaskField $eventTaskField,
        public array $loggingPropertyFields,
    ) {}
}
