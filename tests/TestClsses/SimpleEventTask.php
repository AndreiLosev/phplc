<?php

namespace Tests\TestClsses;

use Phplc\Core\Attributes\EventTask;
use Phplc\Core\Contracts\Task;

#[EventTask('testevent')]
class SimpleEventTask implements Task
{
    public  function  execute(): void
    {
        print_r('OK' . PHP_EOL);
    }
}
