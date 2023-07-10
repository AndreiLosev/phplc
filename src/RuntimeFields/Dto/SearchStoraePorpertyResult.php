<?php

namespace Phplc\Core\RuntimeFields\Dto;

class SearchStoraePorpertyResult
{
    /** 
     * @param array<string, RetainPropertyField[]> $retainPropertyFields
     * @param array<string, LoggingPropertyField[]> $loggingPropertyField 
     */
    public function __construct(
        public array $retainPropertyFields,
        public array $loggingPropertyField,
    ) {}
}
