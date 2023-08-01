<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Attributes\ChangeTracking;
use Phplc\Core\Attributes\EventTask;
use Phplc\Core\Attributes\Logging;
use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Attributes\Retain;
use Phplc\Core\Contracts\JsonObject;
use Phplc\Core\RuntimeFields\Dto\EventTaskBuildResult;
use Phplc\Core\RuntimeFields\Dto\EventTaskFieldDto;
use Phplc\Core\RuntimeFields\Dto\PeriodicTaskBuildResult;
use Phplc\Core\RuntimeFields\Dto\PeriodicTaskFieldDto;
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
                $result->changeTrackingField = $buildResult->changeTrackingField;

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
                $result->changeTrackingField = $buildResult->changeTrackingField;
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

        $changeTrackingProperty = [
            ...$searchResult->changeTrackingPropertyFields,
            $reflectionClass->getName() => $taskPropertyFields->changeTrackingProperty,
        ];

        $period = $attributInstans->seconds + $attributInstans->milliseconds / 1000;

        $periodicTasField = new PeriodicTaskFieldDto(
            $taskInstans,
            $period,
            $taskPropertyFields->retainProeprty,
            $searchResult->retainPropertyFields,
        );

        return new PeriodicTaskBuildResult(
            $periodicTasField,
            $loggingPropertyFields,
            $changeTrackingProperty,
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

        $changeTrackingProperty = [
            ...$searchResult->changeTrackingPropertyFields,
            $reflectionClass->getName() => $taskPropertyFields->changeTrackingProperty,
        ];

        $periodicTasField = new EventTaskFieldDto(
            $taskInstans,
            $attributInstans->eventName,
            $taskPropertyFields->retainProeprty,
            $searchResult->retainPropertyFields,
        );

        return new EventTaskBuildResult(
            $periodicTasField,
            $loggingPropertyFields,
            $changeTrackingProperty,
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

                if (!class_exists($typeName)) {
                    continue;
                }

                if (!in_array(Storage::class, class_implements($typeName))) {
                    continue;
                }

                /** @var class-string<Storage> $typeName */
                $reflectStorageClass = new ReflectionClass($typeName);
                $storagPropertyFields[$typeName] = $this
                    ->searchPropertyAttriburs($reflectStorageClass);
            }
        } 

        $storagPropertyRetainFields = [];
        $storagPropertyChangeTrackingFields = [];
        foreach ($storagPropertyFields as $key => $field) {
            $storagPropertyRetainFields[$key] = $field->retainProeprty;
            $storagPropertyChangeTrackingFields[$key] = $field->changeTrackingProperty;
        }

        $loggingPropertyFields = [];
        foreach ($storagPropertyFields as $key => $field) {
            $loggingPropertyFields[$key] = $field->loggingProperty;
        }

        return new SearchStoraePorpertyResult(
            $storagPropertyRetainFields,
            $loggingPropertyFields,
            $storagPropertyChangeTrackingFields,
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
        $changeTrackingProperty = [];

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

                if ($propertyAttribut->getName() === ChangeTracking::class) {
                    $propertyAttributInstans = $propertyAttribut->newInstance();
                    if ($propertyAttributInstans instanceof ChangeTracking) {
                        $changeTrackingProperty[] = $this->changeTrackingPropretyBuild(
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
            $changeTrackingProperty,
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

        $validTypes = ['bool', 'int', 'float', 'string', 'array', JsonObject::class];
        if (!$this->checkType($property->getType(), $validTypes)) {
            $validTypesStr = implode(', ', $validTypes);
            $mess = "retain \"{$propertyName}\" type must be set valid types {$validTypesStr}";
            throw new \RuntimeException($mess);
        }

        if (!$property->isPublic()) {
            $getter = $attributInstans->getter;
            $setter = $attributInstans->setter;
            if (is_null($setter) || is_null($getter)) {
                $mess = "Retain \"{$propertyName}\" must be public or provide public getter methods";
                throw new \RuntimeException($mess);
            }
            $condition = method_exists($class->getName(), $getter)
                && method_exists($class->getName(), $setter); 
            if (!$condition) {
                $mess = "Retain \"{$propertyName}\" must be public or provide public getter methods";
                throw new \RuntimeException($mess);

            }
            $refletionGetter = new ReflectionMethod($class->getName(), $getter);
            $refletionSetter = new ReflectionMethod($class->getName(), $setter);

            if (!($refletionGetter->isPublic() && $refletionSetter->isPublic())) {
                $mess = "Retain \"{$propertyName}\" must be public or provide public getter methods";
                throw new \RuntimeException($mess);
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
        $validTypes = ['bool', 'int', 'float', 'string'];
        if (!$this->checkType($property->getType(), $validTypes)) {
            $validTypesStr = implode(', ', $validTypes);
            $mess = "logging \"{$propertyName}\" type must be set valid types {$validTypesStr}";
            throw new \RuntimeException($mess);
        }

        $getter = null;
         if (!$property->isPublic()) {
            $getter = $attributInstans->getter;
            if (is_null($getter)) {
                $mess = "logging \"{$propertyName}\" must be public or provide public getter methods";
                throw new \RuntimeException($mess);
            }

            if (!method_exists($class->getName(), $getter)) {
                $mess = "Logging \"{$propertyName}\" must be public or provide public getter methods";
                throw new \RuntimeException($mess);
            }

            $refletionMetod = new ReflectionMethod($class->getName(), $getter);
            if (!$refletionMetod->isPublic()) {
                $mess = "Logging \"{$propertyName}\" must be public or provide public getter methods";
                throw new \RuntimeException($mess);
            }
        }

        return new LoggingPropertyField($propertyName, $getter);
    }

    /** 
     * @param  ReflectionClass<Task|Storage> $class 
     */
    private function changeTrackingPropretyBuild(
        ReflectionProperty $property,
        ChangeTracking $attributInstans,
        ReflectionClass $class,
    ): ChangeTrackingField {
        $propertyName = $property->getName();
        $event = $attributInstans->event;
        $getter = null;

        $validTypes = ['bool', 'int', 'float', 'string'];
        if (!$this->checkType($property->getType(), $validTypes)) {
            $validTypesStr = implode(', ', $validTypes);
            $mess = "ChangeTracking \"{$propertyName}\" type must be set valid types {$validTypesStr}";
            throw new \RuntimeException($mess);
        }

         if (!$property->isPublic()) {
            $getter = $attributInstans->getter;
            if (is_null($getter)) {
                $mess = "ChangeTracking \"{$propertyName}\" must be public or provide public getter methods";
                throw new \RuntimeException($mess);
            }

            if (!method_exists($class->getName(), $getter)) {
                $mess = "ChangeTracking \"{$propertyName}\" must be public or provide public getter methods";
                throw new \RuntimeException($mess);
            }

            $refletionMetod = new ReflectionMethod($class->getName(), $getter);
            if (!$refletionMetod->isPublic()) {
                $mess = "ChangeTracking \"{$propertyName}\" must be public or provide public getter methods";
                throw new \RuntimeException($mess);
            }
        }

        return new ChangeTrackingField($propertyName, $event, $getter);
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

    /** 
     * @param string[] $validTypes 
     */
    private function checkType(
        null|\ReflectionType $type,
        array $validTypes,
    ): bool
    {
        if (is_null($type)) {
            return false;
        }

        if ($type instanceof \ReflectionNamedType) {
            $typeName = $type->getName();
            return in_array($typeName, $validTypes);
        }

        return false;
    }
}
