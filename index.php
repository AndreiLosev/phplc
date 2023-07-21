<?php

use Illuminate\Container\Container;
use Phplc\Core\Attributes\EventTask;
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

#[EventTask('print')]
class Test implements Task
{
    public function execute(): void
    {
        print_r('> run <' . PHP_EOL);
    }
}

$container = new Container();
$plc = new Runtime([
    Test::class,
], $container);

$plc->build();
$plc->run();

