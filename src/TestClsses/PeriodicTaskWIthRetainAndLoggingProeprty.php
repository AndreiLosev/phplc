<?php

namespace Phplc\Core\TestClsses;

use Phplc\Core\Attributes\Logging;
use Phplc\Core\Attributes\PeriodicTask;
use Phplc\Core\Attributes\Retain;
use Phplc\Core\Contracts\Task;

#[PeriodicTask]
class PeriodicTaskWIthRetainAndLoggingProeprty implements Task
{
    #[Retain]
    public  int $q1 = 1;

    #[Retain('setQ2', 'getQ2')]
    private  string $q2 = '2';

    #[Retain]
    #[Logging]
    public bool $q3 = false;


    #[Logging('getQ4')]
    protected float $q4 = 0.1;

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

    public  function  __invoke(): void
    {
        print_r('OK' . PHP_EOL);
    }
}
