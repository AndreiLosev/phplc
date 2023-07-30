<?php

namespace Tests\TestClsses;

use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Contracts\Task;

#[PeriodicTask(0, 30)]
class ThrowableTask implements Task
{
    public function execute(): void
    {
        throw new \RuntimeException("Test RuntimeException", 159);
    }
}
