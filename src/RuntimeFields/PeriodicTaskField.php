<?php

namespace Phplc\Core\RuntimeFields;

use Amp;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;

class PeriodicTaskField
{
    private float $startTime;
    private bool $cancelToken;

    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param ChangeTrackingField[] $taskChangeTrackingPropertus
     * @param array<class-string<Storage>, RetainPropertyField[]> $storageRetainProerty
     * @param array<class-string<Storage>, ChangeTrackingField[]> $storageChangeTrackingProerty
     */
    public function __construct(
        private Task $task,
        private float $periodMilis,
        private array $taskRetainPropertus,
        private array $storageRetainProerty,
        private array $taskChangeTrackingPropertus,
        private array $storageChangeTrackingProerty,
    ) {
        $this->startTime = 0;
        $this->cancelToken = false;
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
                // TODO retain property
            } catch (\Throwable $th) {
                //TODO;
            }

            \Amp\delay($this->getDelay());
        }
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
