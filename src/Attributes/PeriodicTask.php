<?php

namespace Phplc\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class PeriodicTask
{
    public function __construct(
        public int $seconds = 0,
        public int $milliseconds = 100,
    ) {}
}
