<?php

namespace Tests\TestClsses;

use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Contracts\Task;

#[PeriodicTask(1, 150)]
class SimplePerioditTask implements Task
{
    public  function  execute(): void
    {
        print_r('OK' . PHP_EOL);
    }
}
