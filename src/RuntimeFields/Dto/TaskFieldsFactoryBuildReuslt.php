<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\EventTaskField;
use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\PeriodicTaskField;

class TaskFieldsFactoryBuildReuslt
{
    /** 
     * @param array<
     *      class-string<Storage|Task>,
     *      LoggingPropertyField[]
     *  > $loggingPropertyFields
     */
    public function __construct(
        public null|PeriodicTaskField $periodicTask = null,
        public null|EventTaskField $eventTask = null,
        public array $loggingPropertyFields = [],
    ) {}
}
