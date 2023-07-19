<?php

namespace Tests;

use Phplc\Core\Container;
use Phplc\Core\RuntimeFields\EventTaskFieldsCollection;
use Phplc\Core\RuntimeFields\LoggingPropertyFieldsCollection;
use Phplc\Core\RuntimeFields\PeriodicTaskFieldsCollection;
use Phplc\Core\Runtime;
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
        $periodicTaskFields = self::getPrivatPropert($periodicTasksColection, 'collection');
        $eventTaskFields = self::getPrivatPropert($eventTasksColection, 'collection');
        $loggingFields = self::getPrivatPropert($loggingFieldsCollection, 'collection');

        return [
            'periodicTaskFields' => $periodicTaskFields,
            'eventTaskFields' => $eventTaskFields,
            'loggingFields' => $loggingFields,
        ];

    }

    public static function getPrivatPropert(mixed $object, string $proeprty): mixed
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($proeprty);
        $proeprtyValue = $reflectionProperty->getValue($object);
        
        return $proeprtyValue;
    }

    public static function getCloseRuntimeClient(): void
    {
        delay(1);
        $socket = connect("127.0.0.1:9191");
        $data = [
            'command' => "Cansel",
            'params' => new \stdClass,
        ];

        $socket->write(json_encode($data));
        $qwe = $socket->read();
        $socket->close();
    }
}
