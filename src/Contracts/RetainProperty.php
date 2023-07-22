<?php

namespace Phplc\Core\Contracts;

interface RetainProperty
{
    /** 
     * @param scalar|array|\stdClass|\Stringable $value 
     */
    public function createIfNotExists(string $name, mixed $value): void;

    /** 
     * @param scalar|array|\stdClass|\Stringable $value 
     */
    public function update(string $name, mixed $value): void;

    /** 
     * @return scalar|array|\stdClass|\JsonSerializable 
     */
    public function select(string $name): mixed;
}
