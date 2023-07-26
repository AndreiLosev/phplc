<?php

namespace Phplc\Core\RuntimeFields\Dto;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\Dto\EventTaskFieldDto;
use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\Dto\PeriodicTaskFieldDto;

class TaskFieldsFactoryBuildReuslt
{
    /** 
     * @param array<
     *      class-string<Storage|Task>,
     *      LoggingPropertyField[]
     *  > $loggingPropertyFields
     */
    public function __construct(
        public null|PeriodicTaskFieldDto $periodicTask = null,
        public null|EventTaskFieldDto $eventTask = null,
        public array $loggingPropertyFields = [],
    ) {}
}
