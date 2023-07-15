<?php

namespace Phplc\Core\RuntimeFields;

// use function Amp\delay;
use Phplc\Core\Contracts\Task;

class PeriodicTaskField
{
    protected int $startTime;
    protected bool $cancelToken;

    /** 
     * @param RetainPropertyField[] $taskRetainPropertus 
     * @param array<class-string, RetainPropertyField[]> $storageRetainProerty
     */
    public function __construct(
        protected Task $task,
        protected int $periodMilis,
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
            } catch (\Throwable $th) {
                //TODO;
            }

            \Amp\delay($this->getDelay());
        }
    }

    private function setStartTime(): void
    {
        $now = hrtime(true);
        $this->startTime = (int)($now / 1000000);
    }

    private function getDelay(): float
    {
        $now = (int)(hrtime(true) / 1000000);
        $executionTime = $now - $this->startTime;
        return ($this->periodMilis - $executionTime) / 1000;
    }
}
