<?php

use Illuminate\Container\Container;
use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\Runtime;
use function Amp\Socket\connect;
use function Amp\async;
use function Amp\delay;

require_once __DIR__ . '/vendor/autoload.php';

class SecondTestStorage implements Storage
{
    public int $value = 0;
}

#[PeriodicTask]
class SecondTestTask1 implements Task
{
    public function __construct(
        public SecondTestStorage $storage,
    ) {}

    public function execute(): void
    {
        if ($this->storage->value < 5) {
            print_r([__CLASS__, $this->storage]);
            $this->storage->value = $this->storage->value + 1;
        }
    }
}

#[PeriodicTask]
class SecondTestTask2 implements Task
{
    public function __construct(
        public SecondTestStorage $storage,
    ) {}

    public function execute(): void
    {
        if ($this->storage->value >= 5 && $this->storage->value < 9) {
            print_r([__CLASS__, $this->storage]);
            $this->storage->value = $this->storage->value + 1;
        }
    }
}

$container = new Container();
$plc = new Runtime([
    SecondTestTask1::class,
    SecondTestTask2::class,
], $container);

$plc->build();

$plcFuture = async($plc->run(...));

$client = async(function() {
    delay(0.68);
    $socket = connect("127.0.0.1:9191");
    $data = [
        'command' => "Cansel",
        'params' => new stdClass,
    ];

    $socket->write(json_encode($data));
    $qwe = $socket->read();
    $socket->close();
});

$plcFuture->await();
$client->await();

$storage = $container->make(SecondTestStorage::class);

print_r($storage);

