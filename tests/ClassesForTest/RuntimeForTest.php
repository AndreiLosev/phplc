<?php

namespace tests\ClassesForTest;

use Phplc\Lib\Runtime;

class RuntimeForTest extends Runtime
{
    public function getAll(): array
    {
        return [
            'periodiTasks' => $this->periodiTasks,
            'eventTasks' => $this->eventTasks,
            'loggingFields' => $this->loggingFields,
        ];
    }
}
