<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;

class LoggingPropertyFieldsCollection
{
    /** 
     * @param array<class-string<Task|Storage>, LoggingPropertyField[]> $collection
     */
    public function __construct(
        private array $collection,
    ) {}
}
