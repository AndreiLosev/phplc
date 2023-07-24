<?php

namespace Phplc\Core\Contracts;

interface LoggingProperty
{
    public function build(): void;

    /** 
     * @param array<string, scalar> $property 
     */
    public function setLog(array $property): void;
}
