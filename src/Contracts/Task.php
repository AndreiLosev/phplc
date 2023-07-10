<?php

namespace Phplc\Core\Contracts;

interface Task
{
    public function __invoke(): void;
}
