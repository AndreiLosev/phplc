<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\RetainPropertyField;

class SearchPropertyAttribursResult
{
    /** 
     * @param RetainPropertyField[] $retainProeprty 
     * @param LoggingPropertyField[] $loggingProperty
     */
    public function __construct(
        public array $retainProeprty,
        public array $loggingProperty,
    ){}
}
