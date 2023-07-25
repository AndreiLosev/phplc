<?php

namespace Tests\TestClsses;

use Phplc\Core\Attributes\ChangeTracking;
use Phplc\Core\Attributes\Logging;
use Phplc\Core\Attributes\Retain;
use Phplc\Core\Contracts\Storage;

class StoreTest1 implements Storage
{
    public function __construct(
        #[Retain]
        #[ChangeTracking('evet-storage')]
        public int $x1 = 5,

        #[Logging('getX2')]
        private float $x2 = 0.55,
    ) {}

    public function getX2(): float
    {
        return $this->x2;
    } 

    public function setX2(float $x): void
    {
        $this->x2 = $x;
    }
}
