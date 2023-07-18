<?php

use Illuminate\Container\Container;
use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Contracts\Task;
use Phplc\Core\Runtime;

require_once __DIR__ . '/vendor/autoload.php';

#[PeriodicTask(2, 0)]
class TestTask implements Task
{
    public function execute(): void
    {
        $now = time();
        print_r("hello: {$now}" . PHP_EOL);
    }
}

#[PeriodicTask(1, 0)]
class TestTask2 implements Task
{
    public function execute(): void
    {
        $now = time();
        print_r("            second test: {$now}" . PHP_EOL);
    }
}

$container = new Container();
$plc = new Runtime([
    TestTask::class,
    TestTask2::class,
], $container);

$plc->build();

$plc->run();
