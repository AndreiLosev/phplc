<?php

namespace Phplc\Core\RuntimeFields;

class LoggingPropertyField
{
    public function __construct(
        public string $name,
        public string $type,
        public null|string $getter,
    ){}
}
