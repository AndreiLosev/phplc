<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\Contracts\JsonObject;

abstract class PropertWithSetter extends PropertyAttribut
{
    public function __construct(
        public string $name,
        public null|string $getter,
        public null|string $setter,
    ) {}

    /** 
    * @param scalar|array|JsonObject $value 
    */
   public function setValue(Task|Storage $class, mixed $value): void
   {
       match ($this->setter) {
           null => $class->{$this->name} = $value,
           default => $class->{$this->setter}($value),
       };
   }
}
