<?php

namespace Tests\TestClsses;

use Phplc\Core\Attributes\EventTask;
use Phplc\Core\Contracts\Task;

#[EventTask('decriment')]
class SeconTestEventTask implements Task
{
    public function __construct(
        public SecondTestStorage $storage,
    ) {}

    public function execute(): void
    {
        $this->storage->value--;
    }
}
