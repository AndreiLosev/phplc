<?php

namespace Phplc\Core\RuntimeFields;

class ChangeTrackingField extends PropertyAttribut
{
    public function __construct(
        public string $name,
        public string $event,
        public null|string $getter,
    ) {}
}
