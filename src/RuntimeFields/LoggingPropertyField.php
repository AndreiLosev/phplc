<?php

namespace Phplc\Core\RuntimeFields;

class LoggingPropertyField extends PropertyAttribut
{
    public function __construct(
        public string $name,
        public null|string $getter,
    ){}
}
