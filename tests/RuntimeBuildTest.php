<?php

namespace tests;

use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Runtime;
use Phplc\Core\RuntimeFields\EventTaskField;
use Phplc\Core\RuntimeFields\LoggingPropertyField;
use Phplc\Core\RuntimeFields\PeriodicTaskField;
use Phplc\Core\RuntimeFields\RetainPropertyField;
use Phplc\Core\TestClsses\PeriodicTaskWIthRetainAndLoggingProeprty;
use Phplc\Core\TestClsses\SimpleEventTask;
use Phplc\Core\TestClsses\SimplePerioditTask;

class RuntimeBuildTest extends TestCase
{
    public function testSimplePeridocTaskAndEventTask(): void
    {
        $container = new Container();
        $runtime = new Runtime([
            SimplePerioditTask::class,
            SimpleEventTask::class,
        ], $container);

        $runtime->build();

        $periodicTaskFields = $this->getPrivatPropert($runtime, 'periodiTasks');
        $eventTaskFields = $this->getPrivatPropert($runtime, 'eventTasks');

        $this->assertTrue(is_array($periodicTaskFields) || count($periodicTaskFields) === 1);
        $this->assertTrue(is_array($eventTaskFields) || count($eventTaskFields) === 1);

        $periodicTaskField = $periodicTaskFields[0];
        $eventTaskField = $eventTaskFields[0];

        $this->assertTrue($periodicTaskField instanceof PeriodicTaskField);
        $this->assertTrue($eventTaskField instanceof EventTaskField);

        $task = $this->getPrivatPropert($periodicTaskField, 'task');
        $preiodMilis = $this->getPrivatPropert($periodicTaskField, 'periodMilis');
        $taskRetainPropertus = $this->getPrivatPropert($periodicTaskField, 'taskRetainPropertus');
        $storageRetainProerty = $this->getPrivatPropert($periodicTaskField, 'storageRetainProerty');

        $this->assertTrue($task instanceof SimplePerioditTask);
        $this->assertTrue($preiodMilis === 1150);
        $this->assertTrue(is_array($taskRetainPropertus) && count($taskRetainPropertus) === 0);
        $this->assertTrue(is_array($storageRetainProerty) && count($storageRetainProerty) === 0);

        $task = $this->getPrivatPropert($eventTaskField, 'task');
        $eventName = $this->getPrivatPropert($eventTaskField, 'eventName');
        $taskRetainPropertus = $this->getPrivatPropert($eventTaskField, 'taskRetainPropertus');
        $storageRetainProerty = $this->getPrivatPropert($eventTaskField, 'storageRetainProerty');

        $this->assertTrue($task instanceof SimpleEventTask);
        $this->assertTrue($eventName === 'testevent');
        $this->assertTrue(is_array($taskRetainPropertus) && count($taskRetainPropertus) === 0);
        $this->assertTrue(is_array($storageRetainProerty) && count($storageRetainProerty) === 0);
    }

    public function testRetainAndLoggingTaskProperty(): void
    {
        $container = new Container();
        $runtime = new Runtime([
            SimplePerioditTask::class,
            SimpleEventTask::class,
            PeriodicTaskWIthRetainAndLoggingProeprty::class,
        ], $container);

        $runtime->build();

        $periodicTaskFields = $this->getPrivatPropert($runtime, 'periodiTasks');
        $eventTaskFields = $this->getPrivatPropert($runtime, 'eventTasks');
        $loggingFields = $this->getPrivatPropert($runtime, 'loggingFields');

        $this->assertSame(count($periodicTaskFields), 2);
        $this->assertSame(count($eventTaskFields), 1);
        $this->assertSame(count($loggingFields), 1);
        $this->assertSame(count($loggingFields[PeriodicTaskWIthRetainAndLoggingProeprty::class]), 2);

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

        foreach ($periodicTaskFields as $field) {
            $task = $this->getPrivatPropert($field, 'task');
            $taskRetainPropertus = $this->getPrivatPropert($field, 'taskRetainPropertus');

            $this->assertTrue(
                $task instanceof SimplePerioditTask
                    || $task instanceof PeriodicTaskWIthRetainAndLoggingProeprty
            );

            if ($task instanceof PeriodicTaskWIthRetainAndLoggingProeprty) {
                $this->assertTrue(is_array($taskRetainPropertus) && count($taskRetainPropertus) === 3);

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

    private function getPrivatPropert(mixed $object, string $proeprty): mixed
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($proeprty);
        $proeprtyValue = $reflectionProperty->getValue($object);
        
        return $proeprtyValue;
    }
}
