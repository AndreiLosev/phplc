<?php

namespace Phplc\Core\Contracts;

interface ErrorLog
{
    public function build(): void;
    public function log(\Throwable $e, bool $isFatal = false): void;
}
