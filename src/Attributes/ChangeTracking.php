<?php

namespace Phplc\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class ChangeTracking
{
    public function __construct(
        public string $event,
        public null|string $getter = null,
    ) {}
}
