<?php

namespace Phplc\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Retain {
    public function __construct(
        public null|string $setter = null,
        public null|string $getter = null,
    ) {}
}
