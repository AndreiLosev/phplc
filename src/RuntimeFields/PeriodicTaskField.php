<?php

namespace Phplc\Core\RuntimeFields;

use Amp;
use Phplc\Core\Contracts\ErrorLog;
use Phplc\Core\Contracts\RetainProperty;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;

class PeriodicTaskField
{
    private float $startTime;
    private bool $cancelToken;
    private readonly RetainPropertyHeandler $retainHeandler;

    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param array<class-string<Storage>, RetainPropertyField[]> $storageRetainProerty
     * @param \Closure(class-string<Storage>): Storage $makeStorage
     */
    public function __construct(
        private readonly Task $task,
        private float $periodMilis,
        private readonly ErrorLog $errLog,
        array $taskRetainPropertus,
        array $storageRetainProerty,
        RetainProperty $retainService,
        \Closure $makeStorage,
    ) {
        $this->startTime = 0;
        $this->cancelToken = false;
        $this->retainHeandler = new RetainPropertyHeandler(
            $taskRetainPropertus,
            $storageRetainProerty,
            $retainService,
            $makeStorage,
        );
    }

    public function cancel(): void
    {
        $this->cancelToken = true;
    }

    public function run(): void
    {
        while (true) {
            if ($this->cancelToken) {
                return;
            }
            $this->setStartTime();

            try {
                $this->task->execute();
                $this->retainHeandler->saveProprty($this->task);
            } catch (\Throwable $e) {
                $this->errLog->log($e);
            }

            \Amp\delay($this->getDelay());
        }
    }

    public function init(): void
    {
        $this->retainHeandler->init($this->task);
    }

    private function setStartTime(): void
    {
        $this->startTime = Amp\now();
    }

    private function getDelay(): float
    {
        $now = Amp\now();
        $executionTime = $now - $this->startTime;
        $delay = $this->periodMilis - $executionTime;
        if ($delay < 0) {
            $delay = 0;
        }
        return $delay;
    }
}
