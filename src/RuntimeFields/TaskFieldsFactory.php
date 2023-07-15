<?php

namespace Phplc\Core\RuntimeFields;

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
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionMethod;

class TaskFieldsFactory 
{
    public function build(
        Task $taskInstans,
    ): TaskFieldsFactoryBuildReuslt {
        $result = new TaskFieldsFactoryBuildReuslt();

        $reflectionClass = new ReflectionClass($taskInstans);
        $attriburs = $reflectionClass->getAttributes();

        foreach ($attriburs as $attribut) {
            $attributInstans = $this->getPeriodicTaskAttibutOrFalse(
                $attribut,
            );
            if ($attributInstans) {
                $buildResult = $this->periodicTaskBuild(
                    $reflectionClass,
                    $attributInstans,
                    $taskInstans,
                );
    
                $result->periodicTask = $buildResult->periodicTaskField;
                $result->loggingPropertyFields = $buildResult->loggingPropertyFields;

            }

            $attributInstans = $this->getEventTaskAttibutOrFalse(
                $attribut,
            );
            if ($attributInstans) {
                $buildResult = $this->eventTaskBuild(
                    $reflectionClass,
                    $attributInstans,
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
     */
    private function periodicTaskBuild(
        ReflectionClass $reflectionClass,
        PeriodicTask $attributInstans,
        Task $taskInstans,
    ): PeriodicTaskBuildResult {
        $taskPropertyFields = $this->searchPropertyAttriburs($reflectionClass);
        $searchResult = $this->searchStoraePorperty($reflectionClass);

        $loggingPropertyFields = [
            ...$searchResult->loggingPropertyField,
            $reflectionClass->getName() => $taskPropertyFields->loggingProperty
        ];

        $period = $attributInstans->seconds * 1000 + $attributInstans->milliseconds;

        $periodicTasField = new PeriodicTaskField(
            $taskInstans,
            $period,
            $taskPropertyFields->retainProeprty,
            $searchResult->retainPropertyFields,
        );

        return new PeriodicTaskBuildResult(
            $periodicTasField,
            $loggingPropertyFields,
        );
    }

    /** 
     * @param ReflectionClass<Task> $reflectionClass 
     */
    private function eventTaskBuild(
        ReflectionClass $reflectionClass,
        EventTask $attributInstans,
        Task $taskInstans,
    ): EventTaskBuildResult {
        $taskPropertyFields = $this->searchPropertyAttriburs($reflectionClass);

        $searchResult = $this->searchStoraePorperty($reflectionClass);

        $loggingPropertyFields = [
            ...$searchResult->loggingPropertyField,
            $reflectionClass->getName() => $taskPropertyFields->loggingProperty,
        ];

        $periodicTasField = new EventTaskField(
            $taskInstans,
            $attributInstans->eventName,
            $taskPropertyFields->retainProeprty,
            $searchResult->retainPropertyFields,
        );

        return new EventTaskBuildResult(
            $periodicTasField,
            $loggingPropertyFields,
        );
    }

    /** 
     * @param ReflectionClass<Task> $reflectionClass
     */
    private function searchStoraePorperty(
        ReflectionClass $reflectionClass,
    ): SearchStoraePorpertyResult
    {
        /** @var array<class-string<Storage>, SearchPropertyAttribursResult> */
        $storagPropertyFields = [];

        $refletionConstructor = $reflectionClass->getConstructor();

        if (!empty($refletionConstructor)) {
            foreach ($refletionConstructor->getParameters() as $parameter) {
                $refletionType = $parameter->getType();
                if (empty($refletionType)) {
                    continue;
                }

                if (!($refletionType instanceof ReflectionNamedType)) {
                    throw new \RuntimeException("unsuported storage Union and Intersection types");
                }

                $typeName = $refletionType->getName();
                
                if (!in_array(Storage::class, class_implements($typeName))) {
                    continue;
                }

                /** @var class-string<Storage> $typeName 
                 */
                if (!class_exists($typeName)) {
                    continue;
                }
                $reflectStorageClass = new ReflectionClass($typeName);
                $storagPropertyFields[$typeName] = $this
                    ->searchPropertyAttriburs($reflectStorageClass);
            }
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
     * @param ReflectionClass<Task|Storage> $class 
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
                    $propertyAttributInstans = $propertyAttribut->newInstance();
                    if ($propertyAttributInstans instanceof Retain) {
                        $retainProperty[] = $this->retainPropertyBuild(
                            $property,
                            $propertyAttributInstans,
                            $class
                        );
                    }                   
                }

                if ($propertyAttribut->getName() === Logging::class) {
                    $propertyAttributInstans = $propertyAttribut->newInstance();
                    if ($propertyAttributInstans instanceof Logging) {
                        $loggingProperty[] = $this->loggingPropretyBuild(
                            $property,
                            $propertyAttributInstans,
                            $class,
                        );
                    }
                }
            }
        }

        return new SearchPropertyAttribursResult(
            $retainProperty,
            $loggingProperty,
        );
    }

    /** 
     * @param ReflectionClass<Task|Storage> $class 
     */
    private function retainPropertyBuild(
        ReflectionProperty $property,
        Retain $attributInstans,
        ReflectionClass $class,
    ): RetainPropertyField {
        $propertyName = $property->getName();
        $getter = null;
        $setter= null;
        if (!$property->isPublic()) {
            $getter = $attributInstans->getter;
            $setter = $attributInstans->setter;
            if (is_null($setter) || is_null($getter)) {
                throw new \RuntimeException('
                    "Retain property \"{$propertyName}\" must be public or provide getter and setter methods"
                ');
            }
            $condition = method_exists($class->getName(), $getter)
                && method_exists($class->getName(), $setter); 
            if (!$condition) {
                throw new \RuntimeException('
                    "Retain \"{$propertyName}\" must be public or provide getter and setter methods"
                ');
            }
            $refletionGetter = new ReflectionMethod($class->getName(), $getter);
            $refletionSetter = new ReflectionMethod($class->getName(), $setter);

            if (!($refletionGetter->isPublic() && $refletionSetter->isPublic())) {
                throw new \RuntimeException('
                    "Retain \"{$propertyName}\" must be public or provide public getter methods"
                ');
            }
        }

        return new RetainPropertyField($propertyName, $getter, $setter);
    }

    /** 
     * @param  ReflectionClass<Task|Storage> $class 
     */
    private function loggingPropretyBuild(
        ReflectionProperty $property,
        Logging $attributInstans,
        ReflectionClass $class,
    ): LoggingPropertyField {
        $propertyName = $property->getName();
        $getter = null;
         if (!$property->isPublic()) {
            $getter = $attributInstans->getter;
            if (is_null($getter)) {
                throw new \RuntimeException('
                    "Logging \"{$propertyName}\" must be public or provide public getter methods"
                ');
            }

            if (!method_exists($class->getName(), $getter)) {
                throw new \RuntimeException('
                    "Retain \"{$propertyName}\" must be public or provide public getter methods"
                ');
            }

            $refletionMetod = new ReflectionMethod($class->getName(), $getter);
            if (!$refletionMetod->isPublic()) {
                throw new \RuntimeException('
                    "Retain {$propertyName} must be public or provide public getter methods"
                ');
            }
        }

        return new LoggingPropertyField($propertyName, $getter);
    }

    /** 
     * @param ReflectionAttribute<object> $attribut 
     */
    private function getPeriodicTaskAttibutOrFalse(
        \ReflectionAttribute $attribut,
    ): PeriodicTask|false {
        if ($attribut->getName() !== PeriodicTask::class) {
            return false;
        }

        $attributInstans = $attribut->newInstance();

        if (!($attributInstans instanceof PeriodicTask)) {
            throw new \RuntimeException("it can't happen");
        }

        return $attributInstans;
    }

    /** 
     * @param ReflectionAttribute<object> $attribut 
     */
    private function getEventTaskAttibutOrFalse(
        \ReflectionAttribute $attribut,
    ): EventTask|false {
        if ($attribut->getName() !== EventTask::class) {
            return false;
        }

        $attributInstans = $attribut->newInstance();

        if (!($attributInstans instanceof EventTask)) {
            throw new \RuntimeException("it can't happen");
        }

        return $attributInstans;
    }
}
