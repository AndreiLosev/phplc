<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\RetainPropertyField;
use Phplc\Core\RuntimeFields\ChangeTrackingField;

class SearchStoraePorpertyResult
{
    /** 
     * @param array<class-string<Storage>, RetainPropertyField[]> $retainPropertyFields
     * @param array<class-string<Storage>, LoggingPropertyField[]> $loggingPropertyField 
     * @param array<class-string<Storage>, ChangeTrackingField[]> $changeTrackingPropertyFields
     */
    public function __construct(
        public array $retainPropertyFields,
        public array $loggingPropertyField,
        public array $changeTrackingPropertyFields,
    ) {}
}
