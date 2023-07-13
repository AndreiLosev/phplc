<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\RetainPropertyField;

class SearchStoraePorpertyResult
{
    /** 
     * @param array<class-string<Storage>, RetainPropertyField[]> $retainPropertyFields
     * @param array<class-string<Storage>, LoggingPropertyField[]> $loggingPropertyField 
     */
    public function __construct(
        public array $retainPropertyFields,
        public array $loggingPropertyField,
    ) {}
}
