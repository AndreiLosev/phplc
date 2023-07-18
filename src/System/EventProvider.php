<?php

namespace Phplc\Core\System;

use Phplc\Core\RuntimeFields\EventTaskFieldsCollection;
use function Amp\delay;

class EventProvider
{
    const REPEAT = '!&..repeat..&!';

    /** 
     * @var string[] 
     */
    private array $queue;

    public function __construct()
    {
        $this->queue = [self::REPEAT];
    }

    public function dispatchEvent(string $event): void
    {
        $this->queue[] = $event;
    }

    public function cancel(): void
    {
        $this->queue = array_filter(
            $this->queue,
            fn(string $v) => $v !== self::REPEAT,
        );   
    }

    /** 
     * @param callable(string):void $innerEventExecutor 
     */
    public function run(
        EventTaskFieldsCollection $collection,
        callable $innerEventExecutor,
    ): void
    {
        while ($event = $this->shift()) {
            if ($this->isRepeat($event)) {
                $this->next();
                continue;
            }

            if ($this->isInnerSystemEvent($event)) {
                $innerEventExecutor($event);
                $this->next();
                continue;
            }

            $collection->run($event);

            $this->next();
        }
    }

    private function shift(): string|null
    {
        $event = array_shift($this->queue); 

        if ($event === self::REPEAT) {
            $this->queue[] = self::REPEAT;
        }

        return $event;
    }

    private function isRepeat(string $event): bool
    {
        return self::REPEAT === $event;
    }

    private function isInnerSystemEvent(string $event): bool
    {
        return is_int(strpos($event, InnerSysteEvents::INNER_PREFIX));
    }

    private function next(): void
    {
        delay(0.01);
    }
}
