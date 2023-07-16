<?php

namespace Phplc\Core\RuntimeFields;

use Amp;
use Phplc\Core\Contracts\Task;

class PeriodicTaskField
{
    protected float $startTime;
    protected bool $cancelToken;

    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param array<class-string, RetainPropertyField[]> $storageRetainProerty
     */
    public function __construct(
        protected Task $task,
        protected float $periodMilis,
        protected array $taskRetainPropertus,
        protected array $storageRetainProerty,
    ) {
        $this->startTime = 0;
        $this->cancelToken = false;
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
        return $this->periodMilis - $executionTime;
    }
}
