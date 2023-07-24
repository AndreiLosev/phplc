<?php

namespace Phplc\Core\Contracts;

interface RetainProperty
{
    /** 
     * @param scalar|array|JsonObject $value 
     */
    public function createIfNotExists(string $name, mixed $value): void;

    /** 
     * @param scalar|array|JsonObject $value 
     */
    public function update(string $name, mixed $value): void;

    /** 
     * @param string[] $names 
     * @return array<string, scalar|array|JsonObject> 
     */
    public function select(array $names): mixed;
}
