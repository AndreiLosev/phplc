<?php

namespace Tests\TestClsses;

use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Contracts\Task;
use Tests\TestClsses\SecondTestStorage;

#[PeriodicTask]
class SecondTestTask1 implements Task
{
    public function __construct(
        public SecondTestStorage $storage,
    ) {}

    public function execute(): void
    {
        if ($this->storage->value < 5) {
            $this->storage->value++;
        }
    }
}
