<?php

namespace Phplc\Core\RuntimeFields;

class LoggingPropertyField
{
    public function __construct(
        public string $name,
        public null|string $getter,
    ){}
}
