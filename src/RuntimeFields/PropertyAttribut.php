<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\Contracts\JsonObject;
use Phplc\Core\Helpers;

abstract class PropertyAttribut
{
    public function __construct(
        public string $name,
        public null|string $getter,
    )
    {}

    /** 
     * @return array{string, scalar|array|JsonObject} 
     */
    public function getKeyValue(Task|Storage $class): array
    {
        $shortName = Helpers::shortName($class::class);
        $key = "{$shortName}::{$this->name}";
        /** @var scalar|array|JsonObject */
        $value = match ($this->getter) {
            null => $class->{$this->name},
            default => $class->{$this->getter}(),
        };

        return [$key, $value];
    }
}
