<?php

use Illuminate\Container\Container;
use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\Runtime;
use Tests\TestClsses\SeconTestEventTask;
use Tests\TestClsses\SecondTestStorage;
use Tests\TestClsses\SecondTestTask1;
use Tests\TestClsses\SecondTestTask2;
use function Amp\Socket\connect;
use function Amp\async;
use function Amp\delay;

require_once __DIR__ . '/vendor/autoload.php';


$container = new Container();
$plc = new Runtime([
    SecondTestTask1::class,
    SecondTestTask2::class,
    SeconTestEventTask::class,
], $container);

$plc->build();

$plcFuture = async($plc->run(...));

// $client = async(function() {
//     delay(0.68);
//     $socket = connect("127.0.0.1:9191");
//     $data = [
//         'command' => "Cansel",
//         'params' => new stdClass,
//     ];

//     $socket->write(json_encode($data));
//     $qwe = $socket->read();
//     $socket->close();
// });

$plcFuture->await();
// $client->await();

$storage = $container->make(SecondTestStorage::class);

print_r($storage);

