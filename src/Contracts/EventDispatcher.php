<?php

namespace Phplc\Core\Contracts;

interface EventDispatcher
{
    public function dispatch(string $event): void;
}
