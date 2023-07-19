<?php

namespace Tests\TestClsses;

use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Contracts\EventDispatcher;
use Phplc\Core\Contracts\Task;
use Tests\TestClsses\SecondTestStorage;

#[PeriodicTask(0, 10)]
class DispatchEventTask implements Task
{
    public int $counter = 0;
    public function __construct(
        public SecondTestStorage $storage,
        private EventDispatcher $dispatcher,
    ) {
        $this->storage->value = 5;
    }

    public function execute(): void
    {
        if ($this->counter < 5) {
            $this->dispatcher->dispatch('decriment');
            $this->counter++;
        }
    }
}
