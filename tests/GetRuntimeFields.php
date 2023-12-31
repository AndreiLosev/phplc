<?php

namespace Tests;

use Illuminate\Container\Container as IlluminateContainer;
use Phplc\Core\Config;
use Phplc\Core\Container;
use Phplc\Core\RuntimeFields\EventTaskFieldsCollection;
use Phplc\Core\RuntimeFields\LoggingPropertyFieldsCollection;
use Phplc\Core\RuntimeFields\PeriodicTaskFieldsCollection;
use Phplc\Core\Runtime;
use Phplc\Core\System\ChangeTrackingStorage;
use Tests\TestClsses\ConfigForTests;
use function Amp\Socket\connect;
use function Amp\delay;

class GetRuntimeFields
{
    /** 
     * @return array<string, mixed> 
     */
    public static function get(Runtime $runtime): array
    {
        /** @var Container */
        $appContainer = self::getPrivatPropert($runtime, 'container');
        $periodicTasksColection = $appContainer->make(PeriodicTaskFieldsCollection::class);
        $eventTasksColection = $appContainer->make(EventTaskFieldsCollection::class);
        $loggingFieldsCollection = $appContainer->make(LoggingPropertyFieldsCollection::class);
        $changeFieldCollection = $appContainer->make(ChangeTrackingStorage::class);
        $periodicTaskFields = self::getPrivatPropert($periodicTasksColection, 'collection');
        $eventTaskFields = self::getPrivatPropert($eventTasksColection, 'collection');
        $loggingFields = self::getPrivatPropert($loggingFieldsCollection, 'collection');
        $changeField = self::getPrivatPropert($changeFieldCollection, 'collection');

        return [
            'periodicTaskFields' => $periodicTaskFields,
            'eventTaskFields' => $eventTaskFields,
            'loggingFields' => $loggingFields,
            'changeField' => $changeField,
        ];

    }

    public static function getPrivatPropert(mixed $object, string $proeprty): mixed
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($proeprty);
        $proeprtyValue = $reflectionProperty->getValue($object);
        
        return $proeprtyValue;
    }

    public static function getCloseRuntimeClient(float $delay = 0.1): void
    {
        delay($delay);
        $socket = connect("127.0.0.1:9191");
        $data = [
            'command' => "Cansel",
            'params' => new \stdClass,
        ];

        $socket->write(json_encode($data));
        $qwe = $socket->read();
        $socket->close();
    }

    public static function getContainer(): IlluminateContainer
    {
        $config = new ConfigForTests();
        $container = new IlluminateContainer();
        $container->singleton(Config::class, fn() => $config);

        return $container;
    }
}
