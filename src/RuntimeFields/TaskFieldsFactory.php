<?php

namespace Phplc\Core\RuntimeFields;

use Illuminate\Container\Container;
use phpDocumentor\Reflection\Types\ClassString;
use Phplc\Core\Attributes\EventTask;
use Phplc\Core\Attributes\Logging;
use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Attributes\Retain;
use Phplc\Core\RuntimeFields\Dto\EventTaskBuildResult;
use Phplc\Core\RuntimeFields\Dto\PeriodicTaskBuildResult;
use Phplc\Core\RuntimeFields\Dto\SearchPropertyAttribursResult;
use Phplc\Core\RuntimeFields\Dto\TaskFieldsFactoryBuildReuslt;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\RuntimeFields\Dto\SearchStoraePorpertyResult;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

class TaskFieldsFactory 
{
    /** 
     * @param ClassString $taskName 
     */
    public function build(
        Task $taskInstans,
    ): TaskFieldsFactoryBuildReuslt
    {
        $result = new TaskFieldsFactoryBuildReuslt();

        $reflectionClass = new ReflectionClass($taskInstans);
        $attriburs = $reflectionClass->getAttributes();

        foreach ($attriburs as $attribut) {
            if ($attribut->getName() === PeriodicTask::class) {
                $buildResult = $this->periodicTaskBuild(
                    $reflectionClass,
                    $attribut,
                    $taskInstans,
                );

                $result->periodicTask = $buildResult->periodicTaskField;
                $result->loggingPropertyFields = $buildResult->loggingPropertyFields;

            }

            if ($attribut->getName() === EventTaskField::class) {
                $buildResult = $this->eventTaskBuild(
                    $reflectionClass,
                    $attribut,
                    $taskInstans,
                );

                $result->eventTask = $buildResult->eventTaskField;
                $result->loggingPropertyFields = $buildResult->loggingPropertyFields;
            }
        }

        return $result;
    }

    /** 
     * @param ReflectionClass<Task> $reflectionClass 
     * @param ReflectionAttribute<PeriodicTask> $reflectionAttribute
     */
    private function periodicTaskBuild(
        ReflectionClass $reflectionClass,
        ReflectionAttribute $reflectionAttribute,
        Task $taskInstans,
    ): PeriodicTaskBuildResult {
        [$seconds, $milliseconds] = $reflectionAttribute->getArguments();
        $taskPropertyFields = $this->searchPropertyAttriburs($reflectionClass);

        $searchResult = $this->searchStoraePorperty($reflectionClass);

        $searchResult->loggingPropertyField[$reflectionClass->getName()]
            = $taskPropertyFields->loggingProperty;

        $periodicTasField = new PeriodicTaskField(
            $taskInstans,
            $seconds * 1000 + $milliseconds,
            $taskPropertyFields->retainProeprty,
            $searchResult->retainPropertyFields,
        );

        return new PeriodicTaskBuildResult(
            $periodicTasField,
            $searchResult->loggingPropertyField,
        );
    }

    /** 
     * @param ReflectionClass<Task> $reflectionClass 
     * @param ReflectionAttribute<EventTask> $reflectionAttribute
     */
    private function eventTaskBuild(
        ReflectionClass $reflectionClass,
        ReflectionAttribute $reflectionAttribute,
        Task $taskInstans,
    ): EventTaskBuildResult {
        [$eventName] = $reflectionAttribute->getArguments();
        $taskPropertyFields = $this->searchPropertyAttriburs($reflectionClass);

        $searchResult = $this->searchStoraePorperty($reflectionClass);

        $searchResult->loggingPropertyField[$reflectionClass->getName()]
            = $taskPropertyFields->loggingProperty;

        $periodicTasField = new EventTaskField(
            $taskInstans,
            $eventName,
            $taskPropertyFields->retainProeprty,
            $searchResult->retainPropertyFields,
        );

        return new EventTaskBuildResult(
            $periodicTasField,
            $searchResult->loggingPropertyField,
        );
    }

    /** 
     * @param ReflectionClass<Task> $reflectionClass
     */
    private function searchStoraePorperty(
        ReflectionClass $reflectionClass,
    ): SearchStoraePorpertyResult
    {
        /** @var array<string, SearchPropertyAttribursResult> */
        $storagPropertyFields = [];

        foreach ($reflectionClass->getConstructor()->getParameters() as $parameter) {
            if (!in_array(Storage::class, class_implements($parameter->getName()))) {
                continue;
            }

            $reflectStorageClass = new ReflectionClass($parameter->getName());
            $storagPropertyFields[$parameter->getName()] = $this
                ->searchPropertyAttriburs($reflectStorageClass);
        }

        $storagPropertyRetainFields = [];
        foreach ($storagPropertyFields as $key => $field) {
            $storagPropertyRetainFields[$key] = $field->retainProeprty;
        }

        $loggingPropertyFields = [];
        foreach ($storagPropertyFields as $key => $field) {
            $loggingPropertyFields[$key] = $field->loggingProperty;
        }

        return new SearchStoraePorpertyResult(
            $storagPropertyRetainFields,
            $loggingPropertyFields,
        );
    }

    /** 
     * @param ReflectionClass $class 
     */
    private function searchPropertyAttriburs(
        ReflectionClass $class,
    ): SearchPropertyAttribursResult {
        $reflectionPropertys = $class->getProperties();

        $retainProperty = [];
        $loggingProperty = [];

        foreach ($reflectionPropertys as $property) {
            $propertyAttributs = $property->getAttributes();
            foreach ($propertyAttributs as $propertyAttribut) {
                if ($propertyAttribut->getName() === Retain::class) {
                    $retainProperty[] = $this->retainPropertyBuild(
                        $property,
                        $propertyAttribut,
                    );                   
                }

                if ($property->getName() === Logging::class) {
                    $loggingProperty[] = $this->loggingPropretyBuild(
                        $property,
                        $propertyAttribut,
                    );
                }
            }
        }

        return new SearchPropertyAttribursResult(
            $retainProperty,
            $loggingProperty,
        );
    }

    /** 
     * @param ReflectionAttribute<Retain> $attribut
     */
    private function retainPropertyBuild(
        ReflectionProperty $property,
        ReflectionAttribute $attribut,
    ): RetainPropertyField {
        $propertyName = $property->getName();
        $propertyType = $property->getType();
        $getter = null;
        $setter= null;
        if (!$property->isPublic()) {
            [$setter, $getter] = $attribut->getArguments();
            if (is_null($setter) || is_null($getter)) {
                throw new \RuntimeException('
                    "Retain {$propertyName} must be public or provide getter and setter methods"
                ');
            }
        }

        return new RetainPropertyField(
            $propertyName,
            $propertyType,
            $setter,
            $getter,
        );
    }

    /** 
     * @param ReflectionAttribute<Logging> $attribut
     */
    private function loggingPropretyBuild(
        ReflectionProperty $property,
        ReflectionAttribute $attribut,
    ): LoggingPropertyField {
        $propertyName = $property->getName();
        $propertyType = $property->getType();
        $getter = null;
         if (!$property->isPublic()) {
            [$getter] = $attribut->getArguments();
            if (is_null($getter)) {
                throw new \RuntimeException('
                    "Retain {$propertyName} must be public or provide getter methods"
                ');
            }
        }

        return new LoggingPropertyField(
            $propertyName,
            $propertyType,
            $getter,
        );
    }
}
