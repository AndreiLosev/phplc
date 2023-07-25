<?php

namespace Tests\TestClsses;

use Phplc\Core\Attributes\ChangeTracking;
use Phplc\Core\Attributes\Logging;
use Phplc\Core\Attributes\Retain;
use Phplc\Core\Contracts\Storage;

class StoreTest2 implements Storage
{
    public function __construct(
        #[Retain('setX3', 'getX3')]
        private int $x3 = 53,

        #[ChangeTracking('event-name', 'getX4')]
        #[Logging ('getX4')]
        private float $x4 = 1.55,
    ) {}

    public function getX3(): int
    {
        return $this->x3;
    } 

    public function setX3(int $x3): void
    {
        $this->x3 = $x3;
    }

    public function getX4(): float
    {
        return $this->x4;
    }

    public function setX4(float $x4): void
    {
        $this->x4 = $x4;
    }
}
