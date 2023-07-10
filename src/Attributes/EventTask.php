<?php

namespace Phplc\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class EventTask
{
    public function __construct(
        public string $eventName,
    ) {}
}
