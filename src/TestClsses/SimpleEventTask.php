<?php

namespace Phplc\Core\TestClsses;

use Phplc\Core\Attributes\EventTask;
use Phplc\Core\Contracts\Task;

#[EventTask('testevent')]
class SimpleEventTask implements Task
{
    public  function  __invoke(): void
    {
        print_r('OK' . PHP_EOL);
    }
}
