<?php

namespace Tests\TestClsses;

use Phplc\Core\Contracts\EventDispatcher;
use Phplc\Core\Attributes\ChangeTracking;
use Phplc\Core\Attributes\Logging;
use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Attributes\Retain;
use Phplc\Core\Contracts\Task;

#[PeriodicTask(0, 10)]
class PeriodicTaskWIthRetainAndLoggingProeprty implements Task
{
    #[ChangeTracking('test-event')]
    #[Retain]
    public  int $q1 = 1;

    #[Retain('setQ2', 'getQ2')]
    private  string $q2 = '2';

    public function __construct(
        private EventDispatcher $dispatcher,
        #[Retain]
        #[Logging]
        public bool $q3 = false,
        #[Logging('getQ4')]
        #[ChangeTracking('tevent', 'getQ4')]
        protected float $q4 = 0.1,
    ) {}

    public function getQ2(): string
    {
        return $this->q2;
    }

    public function setQ2(string $q2): void
    {
        $this->q2 = $q2;
    }

    public function getQ4(): float
    {
        return $this->q4;
    }

    public  function  execute(): void
    {
        $this->q4 = $this->q4 + 0.1;
        $this->q3 = !$this->q3;
        $this->q1++;
        $this->q2 .= '!';
        $this->dispatcher->dispatch('3-test');

    }
}
