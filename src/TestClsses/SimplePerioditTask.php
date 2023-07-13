<?php

namespace Phplc\Core\TestClsses;

use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Contracts\Task;

#[PeriodicTask(1, 150)]
class SimplePerioditTask implements Task
{
    public  function  __invoke(): void
    {
        print_r('OK' . PHP_EOL);
    }
}
