<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\PeriodicTaskField;

class PeriodicTaskBuildResult
{
    /** 
     * @param array<string, LoggingPropertyField[]> $loggingPropertyFields 
     */
    public function __construct(
        public PeriodicTaskField $periodicTaskField,
        public array $loggingPropertyFields,
    ) {}
}
