<?php

namespace tests;

use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Phplc\Core\Runtime;
use Phplc\Core\RuntimeFields\EventTaskField;
use Phplc\Core\RuntimeFields\PeriodicTaskField;
use Phplc\Core\TestClsses\SimpleEventTask;
use Phplc\Core\TestClsses\SimplePerioditTask;

class RuntimeBuildTest extends TestCase
{
    public function testSimplePeridocTask(): void
    {
        $container = new Container();
        $runtime = new Runtime(
            [SimplePerioditTask::class],
            $container,
        );

        $runtime->build();

        $periodicTaskFields = $this->getPrivatPropert($runtime, 'periodiTasks');

        $this->assertTrue(is_array($periodicTaskFields) || count($periodicTaskFields) === 1);

        $periodicTaskField = $periodicTaskFields[0];

        $this->assertTrue($periodicTaskField instanceof PeriodicTaskField);

        $task = $this->getPrivatPropert($periodicTaskField, 'task');
        $preiodMilis = $this->getPrivatPropert($periodicTaskField, 'periodMilis');
        $taskRetainPropertus = $this->getPrivatPropert($periodicTaskField, 'taskRetainPropertus');
        $storageRetainProerty = $this->getPrivatPropert($periodicTaskField, 'storageRetainProerty');

        $this->assertTrue($task instanceof SimplePerioditTask);
        $this->assertTrue($preiodMilis === 1150);
        $this->assertTrue(is_array($taskRetainPropertus) && count($taskRetainPropertus) === 0);
        $this->assertTrue(is_array($storageRetainProerty) && count($storageRetainProerty) === 0);
    }

    public  function  testSimpleEventTask(): void
    {
        $container = new Container();
        $runtime = new Runtime(
            [SimpleEventTask::class],
            $container,
        );

        $runtime->build();

        $eventTaskFields = $this->getPrivatPropert($runtime, 'eventTasks');

        $this->assertTrue(is_array($eventTaskFields) || count($eventTaskFields) === 1);

        $eventTaskField = $eventTaskFields[0];

        $this->assertTrue($eventTaskField instanceof EventTaskField);

        $task = $this->getPrivatPropert($eventTaskField, 'task');
        $eventName = $this->getPrivatPropert($eventTaskField, 'eventName');
        $taskRetainPropertus = $this->getPrivatPropert($eventTaskField, 'taskRetainPropertus');
        $storageRetainProerty = $this->getPrivatPropert($eventTaskField, 'storageRetainProerty');

        $this->assertTrue($task instanceof SimpleEventTask);
        $this->assertTrue($eventName === 'testevent');
        $this->assertTrue(is_array($taskRetainPropertus) && count($taskRetainPropertus) === 0);
        $this->assertTrue(is_array($storageRetainProerty) && count($storageRetainProerty) === 0);

    }

    private function getPrivatPropert(mixed $object, string $proeprty): mixed
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($proeprty);
        $proeprtyValue = $reflectionProperty->getValue($object);
        
        return $proeprtyValue;
    }
}
