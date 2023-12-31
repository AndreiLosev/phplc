<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Contracts\ErrorLog;
use Phplc\Core\Contracts\RetainProperty;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;

class EventTaskField
{
    private readonly RetainPropertyHeandler $retainHeandler;

    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param array<class-string<Storage>, RetainPropertyField[]> $storageRetainProerty
     * @param \Closure(class-string<Storage>): Storage $makeStorage
     */
    public function __construct(
        private readonly Task $task,
        private readonly string $eventName, 
        private readonly ErrorLog $errLog,
        array $taskRetainPropertus,
        array $storageRetainProerty,
        RetainProperty $retainService,
        \Closure $makeStorage,
    ) {
        $this->retainHeandler = new RetainPropertyHeandler(
            $taskRetainPropertus,
            $storageRetainProerty,
            $retainService,
            $makeStorage,
        );
    }

    public function match(string $eventName): bool
    {
        return $this->eventName === $eventName;
    }

    public function run(): void
    {
        try {
            $this->task->execute();
            $this->retainHeandler->saveProprty($this->task);
        } catch (\Throwable $e) {
            $this->errLog->log($e);
        }
    }

    public function init(): void
    {
        $this->retainHeandler->init($this->task);
    }
}
