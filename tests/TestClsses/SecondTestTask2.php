<?php

namespace Tests\TestClsses;

use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Contracts\Task;
use Tests\TestClsses\SecondTestStorage;

#[PeriodicTask(0, 10)]
class SecondTestTask2 implements Task
{
    public function __construct(
        public SecondTestStorage $storage,
    ) {}

    public function execute(): void
    {
        if ($this->storage->value >= 5 && $this->storage->value < 9) {
            $this->storage->value++;
        }
    }
}
