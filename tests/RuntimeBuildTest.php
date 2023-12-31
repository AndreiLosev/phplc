<?php

namespace Tests;

use Tests\GetRuntimeFields;
use PHPUnit\Framework\TestCase;
use Phplc\Core\Runtime;
use Phplc\Core\RuntimeFields\ChangeTrackingField;
use Phplc\Core\RuntimeFields\EventTaskField;
use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\PeriodicTaskField;
use Phplc\Core\RuntimeFields\RetainPropertyField;
use Tests\TestClsses\EvenTaskWithStores;
use Tests\TestClsses\PeriodicTaskWIthRetainAndLoggingProeprty;
use Tests\TestClsses\SimpleEventTask;
use Tests\TestClsses\SimplePerioditTask;
use Tests\TestClsses\StoreTest1;
use Tests\TestClsses\StoreTest2;


class RuntimeBuildTest extends TestCase
{
    public function testSimplePeridocTaskAndEventTask(): void
    {
        $container = GetRuntimeFields::getContainer();
        $runtime = new Runtime([
            SimplePerioditTask::class,
            SimpleEventTask::class,
        ], $container);

        $runtime->build();

        [
            'periodicTaskFields' => $periodicTaskFields,
            'eventTaskFields' => $eventTaskFields,
        ] = GetRuntimeFields::get($runtime);


        $this->assertTrue(is_array($periodicTaskFields) || count($periodicTaskFields) === 1);
        $this->assertTrue(is_array($eventTaskFields) || count($eventTaskFields) === 1);

        $periodicTaskField = $periodicTaskFields[0];
        $eventTaskField = $eventTaskFields[0];

        $this->assertTrue($periodicTaskField instanceof PeriodicTaskField);
        $this->assertTrue($eventTaskField instanceof EventTaskField);

        $task = GetRuntimeFields::getPrivatPropert($periodicTaskField, 'task');
        $preiodMilis = GetRuntimeFields::getPrivatPropert($periodicTaskField, 'periodMilis');
        $retainHeandler = GetRuntimeFields::getPrivatPropert($periodicTaskField, 'retainHeandler');
        $taskRetainPropertus = GetRuntimeFields::getPrivatPropert($retainHeandler, 'taskRetainPropertus');
        $storageRetainProerty = GetRuntimeFields::getPrivatPropert($retainHeandler, 'storageRetainProerty');

        $this->assertTrue($task instanceof SimplePerioditTask);
        $this->assertSame($preiodMilis, 1.150);
        $this->assertTrue(is_array($taskRetainPropertus) && count($taskRetainPropertus) === 0);
        $this->assertTrue(is_array($storageRetainProerty) && count($storageRetainProerty) === 0);

        $task = GetRuntimeFields::getPrivatPropert($eventTaskField, 'task');
        $eventName = GetRuntimeFields::getPrivatPropert($eventTaskField, 'eventName');
        $retainHeandler = GetRuntimeFields::getPrivatPropert($eventTaskField, 'retainHeandler');
        $taskRetainPropertus = GetRuntimeFields::getPrivatPropert($retainHeandler, 'taskRetainPropertus');
        $storageRetainProerty = GetRuntimeFields::getPrivatPropert($retainHeandler, 'storageRetainProerty');

        $this->assertTrue($task instanceof SimpleEventTask);
        $this->assertTrue($eventName === 'testevent');
        $this->assertTrue(is_array($taskRetainPropertus) && count($taskRetainPropertus) === 0);
        $this->assertTrue(is_array($storageRetainProerty) && count($storageRetainProerty) === 0);
    }

    public function testRetainAndLoggingTaskProperty(): void
    {
        $container = GetRuntimeFields::getContainer();
        $runtime = new Runtime([
            SimplePerioditTask::class,
            SimpleEventTask::class,
            PeriodicTaskWIthRetainAndLoggingProeprty::class,
        ], $container);

        $runtime->build();

        [
            'periodicTaskFields' => $periodicTaskFields,
            'eventTaskFields' => $eventTaskFields,
            'loggingFields' => $loggingFields,
            'changeField' => $changeField,
        ] = GetRuntimeFields::get($runtime);

        $this->assertSame(count($periodicTaskFields), 2);
        $this->assertSame(count($eventTaskFields), 1);
        $this->assertSame(count($loggingFields), 1);
        $this->assertSame(count($loggingFields[PeriodicTaskWIthRetainAndLoggingProeprty::class]), 2);
        $this->assertSame(count($changeField[PeriodicTaskWIthRetainAndLoggingProeprty::class]), 2);

        foreach ($loggingFields[PeriodicTaskWIthRetainAndLoggingProeprty::class] as $loggingField) {
            $this->assertTrue($loggingField instanceof LoggingPropertyField);
            if ($loggingField->name === 'q3') {
                $this->assertTrue(is_null($loggingField->getter));
            } else if ($loggingField->name === 'q4') {
                $this->assertTrue($loggingField->getter === 'getQ4');
            } else {
                $this->assertTrue(false);
            }
        }

        foreach ($changeField[PeriodicTaskWIthRetainAndLoggingProeprty::class] as $changeTracking) {
            $this->assertTrue($changeTracking instanceof ChangeTrackingField);
            if ($changeTracking->name === 'q1') {
                $this->assertTrue(is_null($changeTracking->getter));
                $this->assertSame($changeTracking->event, 'test-event');
            } else if ($changeTracking->name === 'q4') {
                $this->assertSame($changeTracking->getter, 'getQ4');
                $this->assertSame($changeTracking->event, 'tevent');
            } else {
                $this->assertTrue(false);
            }
        }


        foreach ($periodicTaskFields as $field) {
            $task = GetRuntimeFields::getPrivatPropert($field, 'task');
            $retainHeandler = GetRuntimeFields::getPrivatPropert($field, 'retainHeandler');
            $taskRetainPropertus = GetRuntimeFields::getPrivatPropert($retainHeandler, 'taskRetainPropertus');

            $this->assertTrue(
                $task instanceof SimplePerioditTask
                    || $task instanceof PeriodicTaskWIthRetainAndLoggingProeprty
            );

            if ($task instanceof PeriodicTaskWIthRetainAndLoggingProeprty) {
                $this->assertSame(count($taskRetainPropertus), 3);

                foreach ($taskRetainPropertus as $retain) {
                    $this->assertTrue($retain instanceof RetainPropertyField);
                    if ($retain->name === 'q1' || $retain->name === 'q3') {
                        $this->assertTrue(is_null($retain->setter));
                        $this->assertTrue(is_null($retain->getter));
                    } else if ($retain->name === 'q2') {
                        $this->assertSame($retain->getter, 'getQ2');
                        $this->assertSame($retain->setter, 'setQ2');
                    } else {
                        $this->assertTrue(false);
                    }
                }
            }
        }
    }

    public function testRetainAndLoggingInStorage(): void
    {
        $container = GetRuntimeFields::getContainer();
        $runtime = new Runtime([
            SimplePerioditTask::class,
            SimpleEventTask::class,
            PeriodicTaskWIthRetainAndLoggingProeprty::class,
            EvenTaskWithStores::class,
        ], $container);

        $runtime->build();

        [
            'periodicTaskFields' => $periodicTaskFields,
            'eventTaskFields' => $eventTaskFields,
            'loggingFields' => $loggingFields,
            'changeField' => $changeField,
        ] = GetRuntimeFields::get($runtime);

        $this->assertSame(count($periodicTaskFields), 2);
        $this->assertSame(count($eventTaskFields), 2);
        $this->assertSame(count($loggingFields), 3);
        $this->assertSame(count($changeField), 3);
        $this->assertSame(count($loggingFields[PeriodicTaskWIthRetainAndLoggingProeprty::class]), 2);
        $this->assertSame(count($loggingFields[StoreTest1::class]), 1);
        $this->assertSame(count($loggingFields[StoreTest2::class]), 1);
        $this->assertSame(count($changeField[PeriodicTaskWIthRetainAndLoggingProeprty::class]), 2);
        $this->assertSame(count($changeField[StoreTest1::class]), 1);
        $this->assertSame(count($changeField[StoreTest2::class]), 1);

        foreach ($loggingFields[StoreTest1::class] as $loggingField) {
            $this->assertTrue($loggingField instanceof LoggingPropertyField);
            if ($loggingField->name === 'x2') {
                $this->assertSame($loggingField->getter, 'getX2');
            } else {
                $this->assertTrue(false);
            }
        }

        foreach ($loggingFields[StoreTest2::class] as $loggingField) {
            $this->assertTrue($loggingField instanceof LoggingPropertyField);
            if ($loggingField->name === 'x4') {
                $this->assertSame($loggingField->getter, 'getX4');
            } else {
                $this->assertTrue(false);
            }
        }

        foreach ($changeField[StoreTest1::class] as $loggingField) {
            $this->assertTrue($loggingField instanceof ChangeTrackingField);

            $this->assertSame($loggingField->name, 'x1');
            $this->assertSame($loggingField->event, 'evet-storage');
            $this->assertNull($loggingField->getter);
        }

        foreach ($changeField[StoreTest2::class] as $loggingField) {
            $this->assertTrue($loggingField instanceof ChangeTrackingField);

            $this->assertSame($loggingField->name, 'x4');
            $this->assertSame($loggingField->event, 'event-name');
            $this->assertSame($loggingField->getter, 'getX4');
        }

        foreach ($eventTaskFields as $field) {
            $task = GetRuntimeFields::getPrivatPropert($field, 'task');

            $this->assertTrue(
                $task instanceof SimpleEventTask
                    || $task instanceof EvenTaskWithStores
            );

            if ($task instanceof EvenTaskWithStores) {
                $retainHeandler = GetRuntimeFields::getPrivatPropert($field, 'retainHeandler');
                $storageRetainProerty = GetRuntimeFields::getPrivatPropert($retainHeandler, 'storageRetainProerty');

                $this->assertSame(count($storageRetainProerty), 2);

                $retainStoreProperty = $storageRetainProerty[StoreTest1::class];

                $this->assertSame($retainStoreProperty[0]->name, 'x1');
                $this->assertNull($retainStoreProperty[0]->setter);
                $this->assertNull($retainStoreProperty[0]->getter);

                $retainStoreProperty = $storageRetainProerty[StoreTest2::class];

                $this->assertSame($retainStoreProperty[0]->name, 'x3');
                $this->assertSame($retainStoreProperty[0]->setter, 'setX3');
                $this->assertSame($retainStoreProperty[0]->getter, 'getX3');
            }
        }
    }
}
