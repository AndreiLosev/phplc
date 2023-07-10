<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\RuntimeFields\EventTaskField;
use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\PeriodicTaskField;

class TaskFieldsFactoryBuildReuslt
{
    /** 
     * @param array<string, LoggingPropertyField[]> $loggingPropertyFields
     */
    public function __construct(
        public null|PeriodicTaskField $periodicTask = null,
        public null|EventTaskField $eventTask = null,
        public array $loggingPropertyFields = [],
    ) {}
}
