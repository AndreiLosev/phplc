<?php

namespace Tests;

use Amp\DeferredCancellation;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Phplc\Core\Config;
use Phplc\Core\Contracts\LoggingProperty;
use Phplc\Core\Runtime;
use Phplc\Core\System\DefaultLoggingPropertyService;
use Tests\TestClsses\DispatchEventTask;
use Tests\TestClsses\EvenTaskWithStores;
use Tests\TestClsses\PeriodicTaskWIthRetainAndLoggingProeprty;
use Tests\TestClsses\SeconTestEventTask;
use Tests\TestClsses\SecondTestTask1;
use Tests\TestClsses\SecondTestTask2;
use Tests\TestClsses\SecondTestStorage;
use function Amp\Future\awaitAll;
use function Amp\Socket\connect;
use function Amp\async;
use function Amp\delay;

class RuntimeRunTest extends TestCase
{
    public function testDataExchangeViaStorage(): void
    {
        $container = new Container();
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
        $container = new Container();
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
        $container = new Container();
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
        $config = new Config;
        $config->loggingPeriod = 0.04;
        $config->logging['dbPath'] = ':memory:';
        $container = new Container();
        $container->singleton(Config::class, fn() => $config);
        $runtime = new Runtime([
            PeriodicTaskWIthRetainAndLoggingProeprty::class,
            EvenTaskWithStores::class,
        ], $container);

        $runtime->build();
        
        $cancel = new DeferredCancellation();

        $runtimeFuture = async($runtime->run(...));

        $client = async(GetRuntimeFields::getCloseRuntimeClient(...));

    
        $runtimeFuture->await();
        $client->await();

        // $cancelFuture = async(function() use ($cancel) {
        //     delay(0.1);
        //     $cancel->cancel();
        // });

        // try {
        //     awaitAll([$runtimeFuture, $cancelFuture], $cancel->getCancellation());
        // } catch (\Throwable $th) {}

        /** 
         * @var DefaultLoggingPropertyService 
         */
        $db = $container->make(LoggingProperty::class);

        $dbResult = $db->query("SELECT * FROM logging_property");

        $result = [];

        while ($row = $dbResult->fetchArray(SQLITE3_ASSOC)) {
            $result[$row['name']][] = $row;
        }

        foreach ($result as $value) {
            $this->assertSame(count($value), 3);
        }
    }
}
