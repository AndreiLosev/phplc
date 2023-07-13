<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\RetainPropertyField;

class SearchStoraePorpertyResult
{
    /** 
     * @param array<class-string, RetainPropertyField[]> $retainPropertyFields
     * @param array<class-string, LoggingPropertyField[]> $loggingPropertyField 
     */
    public function __construct(
        public array $retainPropertyFields,
        public array $loggingPropertyField,
    ) {}
}
