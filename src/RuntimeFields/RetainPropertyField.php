<?php

namespace Phplc\Core\RuntimeFields;

class RetainPropertyField  
{
    public function __construct(
        public string $name,
        public null|string $getter,
        public null|string $setter,
    ){}
}