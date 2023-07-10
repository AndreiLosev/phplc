<?php

namespace Phplc\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Logging {
    public function __construct(
        public null|string $getter = null,
    ) {}
}
