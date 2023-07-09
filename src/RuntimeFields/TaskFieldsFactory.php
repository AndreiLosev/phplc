<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Attributes\PeriodicTask;
use ReflectionAttribute;
use ReflectionClass;

class TaskFieldsFactory 
{
    public function __construct(
        private string $taskClassName,
    ) {}

    public function build(): void
    {
        $reflectionClass = new ReflectionClass($this->taskClassName);

        $attriburs = $reflectionClass->getAttributes();

        foreach ($attriburs as $attribut) {
            if ($attribut->getName() === PeriodicTask::class) {

            }
        }
        
    }

    private function PeriodicTaskBuild(
        ReflectionClass $reflectionClass,
        ReflectionAttribute $reflectionAttribute,
    ): PeriodicTaskField {

    }
}
