<?php

namespace Phplc\Core\RuntimeFields;

use Amp;
use Phplc\Core\Contracts\ErrorLog;
use Phplc\Core\Contracts\EventDispatcher;
use Phplc\Core\Contracts\RetainProperty;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\System\ChangeTrackingStorage;

class PeriodicTaskField
{
    private float $startTime;
    private bool $cancelToken;
    private readonly RetainPropertyHeandler $retainHeandler;
    private readonly ChangeTrackingFieldHeandler $changeTrackingHeandler;

    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param ChangeTrackingField[] $taskChangeTrackingPropertus
     * @param array<class-string<Storage>, RetainPropertyField[]> $storageRetainProerty
     * @param array<class-string<Storage>, ChangeTrackingField[]> $storageChangeTrackingProerty
     * @param \Closure(class-string<Storage>): Storage $makeStorage
     */
    public function __construct(
        private readonly Task $task,
        private float $periodMilis,
        private readonly ErrorLog $errLog,
        array $taskRetainPropertus,
        array $storageRetainProerty,
        array $taskChangeTrackingPropertus,
        array $storageChangeTrackingProerty,
        RetainProperty $retainService,
        \Closure $makeStorage,
        ChangeTrackingStorage $changeTrackingStorage,
        EventDispatcher $eventDispatcher,
    ) {
        $this->startTime = 0;
        $this->cancelToken = false;
        $this->retainHeandler = new RetainPropertyHeandler(
            $taskRetainPropertus,
            $storageRetainProerty,
            $retainService,
            $makeStorage,
        );

        $this->changeTrackingHeandler = new ChangeTrackingFieldHeandler(
            $taskChangeTrackingPropertus,
            $storageChangeTrackingProerty,
            $changeTrackingStorage,
            $eventDispatcher,
            $makeStorage,
        );
    }

    public function cancel(): void
    {
        $this->cancelToken = true;
    }

    /** 
     * @param class-string<Task> 
     */
    public function taskIs(string $taskName): bool
    {
        return $this->task::class === $taskName;
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
                $this->changeTrackingHeandler->heandler($this->task);
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
