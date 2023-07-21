<?php

namespace Tests;

use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Phplc\Core\Runtime;
use Tests\TestClsses\DispatchEventTask;
use Tests\TestClsses\SeconTestEventTask;
use Tests\TestClsses\SecondTestTask1;
use Tests\TestClsses\SecondTestTask2;
use Tests\TestClsses\SecondTestStorage;
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
}