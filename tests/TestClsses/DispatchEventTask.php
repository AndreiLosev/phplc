<?php

namespace Tests\TestClsses;

use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Contracts\EventDispatcher;
use Phplc\Core\Contracts\Task;
use Tests\TestClsses\SecondTestStorage;
use function Amp\delay;

#[PeriodicTask(0, 100)]
class DispatchEventTask implements Task
{
    public function __construct(
        public SecondTestStorage $storage,
        private EventDispatcher $dispatcher,
    ) {
        $this->storage->value = 5;
    }

    public function execute(): void
    {
        if ($this->storage->value > 0) {
            $this->dispatcher->dispatch('decriment');
        }
    }
}
