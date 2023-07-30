<?php

namespace Tests;

use Phplc\Core\Contracts\ErrorLog;
use Phplc\Core\Contracts\RetainProperty;
use Phplc\Core\RuntimeFields\EventTaskFieldsCollection;
use Phplc\Core\RuntimeFields\PeriodicTaskFieldsCollection;
use Phplc\Core\System\DefaultErrorLog;
use Phplc\Core\System\DefaultRetainPropertyService;
use Tests\GetRuntimeFields;
use PHPUnit\Framework\TestCase;
use Phplc\Core\Contracts\LoggingProperty;
use Phplc\Core\Runtime;
use Phplc\Core\System\DefaultLoggingPropertyService;
use Tests\TestClsses\ConfigForTests;
use Tests\TestClsses\DispatchEventTask;
use Tests\TestClsses\EvenTaskWithStores;
use Tests\TestClsses\EventChangeTraking;
use Tests\TestClsses\PeriodicTaskWIthRetainAndLoggingProeprty;
use Tests\TestClsses\SeconTestEventTask;
use Tests\TestClsses\SecondTestTask1;
use Tests\TestClsses\SecondTestTask2;
use Tests\TestClsses\SecondTestStorage;
use Tests\TestClsses\StoreTest1;
use Tests\TestClsses\StoreTest2;
use Tests\TestClsses\ThrowableTask;
use function Amp\Socket\connect;
use function Amp\async;
use function Amp\delay;

class RuntimeRunTest extends TestCase
{
    public function testDataExchangeViaStorage(): void
    {
        $container = GetRuntimeFields::getContainer();
        $runtime = new Runtime([
            SecondTestTask1::class,
            SecondTestTask2::class,
        ], $container);

        $runtime->build();

        $future1 = async($runtime->run(...));

        $client = async(GetRuntimeFields::getCloseRuntimeClient(...));

        $future1->await();
        $client->await();

        $storage = $container->make(SecondTestStorage::class);

        $this->assertSame($storage->value, 9);
    }

    public function testEventTaskRun(): void
    {
        $container = GetRuntimeFields::getContainer();
        $runtime = new Runtime([
            DispatchEventTask::class,
            SeconTestEventTask::class,
        ], $container);

        $runtime->build();

        $future1 = async($runtime->run(...));

        $client = async(GetRuntimeFields::getCloseRuntimeClient(...));

        $future1->await();
        $client->await();

        $storage = $container->make(SecondTestStorage::class);

        $this->assertSame($storage->value, 0);
    }

    public function testCommandDispatchEvent(): void
    {
        $container = GetRuntimeFields::getContainer();
        $runtime = new Runtime([
            SeconTestEventTask::class,
        ], $container);

        $runtime->build();

        $future1 = async($runtime->run(...));

        $future2 = async(function() {
            delay(0);
            $socket = connect("127.0.0.1:9191");
            $data = [
                'command' => "DispatchEvent",
                'params' => ["event" => "decriment"],
            ];

            $socket->write(json_encode($data));
            $qwe = $socket->read();
            $socket = connect("127.0.0.1:9191");
            $socket->write(json_encode($data));
            $qwe = $socket->read();
            $socket->close();

        });

        $client = async(GetRuntimeFields::getCloseRuntimeClient(...));

        $future1->await();
        $future2->await();
        $client->await();

        $storage = $container->make(SecondTestStorage::class);

        $this->assertSame($storage->value, -2);
    }

    public function testLoggingProperty(): void
    {
        $container = GetRuntimeFields::getContainer();
        $runtime = new Runtime([
            PeriodicTaskWIthRetainAndLoggingProeprty::class,
            EvenTaskWithStores::class,
        ], $container);

        $runtime->build();
        
        $runtimeFuture = async($runtime->run(...));

        $client = async(GetRuntimeFields::getCloseRuntimeClient(...), 0.15);

        $runtimeFuture->await();
        $client->await();

        /** 
         * @var DefaultLoggingPropertyService 
         */
        $db = $container->make(LoggingProperty::class);

        $dbResult = $db->query("SELECT * FROM logging_property");

        $result = [];

        while ($row = $dbResult->fetchArray(SQLITE3_ASSOC)) {
            $result[$row['name']][] = $row;
        }

        foreach ($result as $name => $value) {
            if ($name === 'PeriodicTaskWIthRetainAndLoggingProeprty::q3') {
                $this->assertTrue(count($value) > 2);
                continue;
            }
            for ($i = 1; $i  < count($value); $i ++) {
                $v1 = (float)$value[$i]['value'];
                $v2 = (float)$value[$i-1]['value']; 
                $this->assertTrue($v1 > $v2);
            }
        }
    }

    public function testRetainPropertyCreate(): void
    {
        $container = GetRuntimeFields::getContainer();
        $runtime = new Runtime([
            PeriodicTaskWIthRetainAndLoggingProeprty::class,
            EvenTaskWithStores::class,
        ], $container);

        $runtime->build();

        $container->make(PeriodicTaskFieldsCollection::class);

        /** @var DefaultRetainPropertyService */
        $db = $container->make(RetainProperty::class);

        $dbR = $db->query("SELECT * from 'retain_property'");

        while ($row = $dbR->fetchArray(SQLITE3_NUM)) {
            [$name, $type, $value] = $row;
            
            if ($name === 'PeriodicTaskWIthRetainAndLoggingProeprty::q1') {
                $this->assertSame('integer', $type);
                $this->assertSame('1', $value);
            }

            if ($name === 'PeriodicTaskWIthRetainAndLoggingProeprty::q2') {
                $this->assertSame('string', $type);
                $this->assertSame('2', $value);
            }

            if ($name === 'PeriodicTaskWIthRetainAndLoggingProeprty::q3') {
                $this->assertSame('boolean', $type);
                $this->assertSame('0', $value);
            }

            if ($name === 'StoreTest1::x1') {
                $this->assertSame('integer', $type);
                $this->assertSame('5', $value);
            }

            if ($name === 'StoreTest2::x3') {
                $this->assertSame('integer', $type);
                $this->assertSame('53', $value);
            }
        }
    }

    public function testRetainPropertyInit(): void
    {
        $config = new ConfigForTests();
        $db = new DefaultRetainPropertyService($config);

        $table = $config->retain['table'];
        $query = "CREATE TABLE IF NOT EXISTS {$table} (
            name TEXT PRIMARY KEY,
            type TEXT,
            value TEXT
            );";

        $db->exec($query);

        $data = [
            'PeriodicTaskWIthRetainAndLoggingProeprty::q1' => 33,
            'PeriodicTaskWIthRetainAndLoggingProeprty::q2' => 'hello world',
            'PeriodicTaskWIthRetainAndLoggingProeprty::q3' => true,
            'StoreTest1::x1' => 696,
            'StoreTest2::x3' => 777,
        ];

        foreach ($data as $name => $value) {
            $db->createIfNotExists($name, $value);
        }

        $container = GetRuntimeFields::getContainer();

        $container->singleton(RetainProperty::class, fn() => $db);

        $runtime = new Runtime([
            PeriodicTaskWIthRetainAndLoggingProeprty::class,
            EvenTaskWithStores::class,
        ], $container);

        $runtime->build();

        $container->make(PeriodicTaskFieldsCollection::class);
        $container->make(EventTaskFieldsCollection::class);

        $periodic = $container->make(PeriodicTaskWIthRetainAndLoggingProeprty::class);
        $storeTest1 = $container->make(StoreTest1::class);
        $storeTest2 = $container->make(StoreTest2::class);

        $this->assertSame(
            $data['PeriodicTaskWIthRetainAndLoggingProeprty::q1'],
            $periodic->q1
        );

        $this->assertSame(
            $data['PeriodicTaskWIthRetainAndLoggingProeprty::q2'],
            $periodic->getQ2(),
        );

        $this->assertSame(
            $data['PeriodicTaskWIthRetainAndLoggingProeprty::q3'],
            $periodic->q3,
        );

        $this->assertSame(
            $data['StoreTest1::x1'],
            $storeTest1->x1,
        );

        $this->assertSame(
            $data['StoreTest2::x3'],
            $storeTest2->getX3(),
        );

        $runtimeFuture = async($runtime->run(...));

        $client = async(GetRuntimeFields::getCloseRuntimeClient(...));

        $runtimeFuture->await();
        $client->await();

        $db = $db->query("SELECT * from 'retain_property'");

        while ($row = $db->fetchArray(SQLITE3_NUM)) {
            [$name, $type, $value] = $row;

            if ($name === 'PeriodicTaskWIthRetainAndLoggingProeprty::q1') {
                $this->assertSame((string)$periodic->q1, $value);
            }

            if ($name === 'PeriodicTaskWIthRetainAndLoggingProeprty::q2') {
                $this->assertSame($periodic->getQ2(), $value);
            }

            if ($name === 'PeriodicTaskWIthRetainAndLoggingProeprty::q3') {
                $this->assertSame((string)(int)$periodic->q3, $value);
            }

            if ($name === 'StoreTest1::x1') {
                $this->assertSame((string)$storeTest1->x1, $value);
            }

            if ($name === 'StoreTest2::x3') {
                $this->assertSame((string)$storeTest2->getX3(), $value);
            }
        }
    }

    public function testChungeTraking(): void
    {
        $container = GetRuntimeFields::getContainer();
        $runtime = new Runtime([
            PeriodicTaskWIthRetainAndLoggingProeprty::class,
            EvenTaskWithStores::class,
            EventChangeTraking::class,
        ], $container);

        $runtime->build();
        
        $runtimeFuture = async($runtime->run(...));

        $client = async(GetRuntimeFields::getCloseRuntimeClient(...));

        $runtimeFuture->await();
        $client->await();

        $periodic = $container->make(PeriodicTaskWIthRetainAndLoggingProeprty::class);
        $evet = $container->make(EventChangeTraking::class);

        $this->assertSame($periodic->q1, $evet->x1 + 1);
    }

    public function testErrorLog(): void
    {
        $container = GetRuntimeFields::getContainer();
        $runtime = new Runtime([
            ThrowableTask::class,
        ], $container);

        $runtime->build();
        
        $runtimeFuture = async($runtime->run(...));

        $client = async(GetRuntimeFields::getCloseRuntimeClient(...));

        $runtimeFuture->await();
        $client->await();

        /** @var DefaultErrorLog */
        $eDb = $container->make(ErrorLog::class);

        $dbResult = $eDb->query("SELECT * FROM error_log");

        while ($row = $dbResult->fetchArray(SQLITE3_ASSOC)) {
            $this->assertSame('Test RuntimeException', json_decode($row['error'])[0]);
        }

    }
}
