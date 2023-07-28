<?php

namespace Tests\TestClsses;

use Phplc\Core\Attributes\EventTask;
use Phplc\Core\Contracts\Task;

#[EventTask('test-event')]
class EventChangeTraking implements Task
{
    public function __construct(
        public int $x1 = 0,
    ) {}

    public function execute(): void
    {
        $this->x1++;
    }
}
