<?php

namespace Phplc\Core\System;

use Phplc\Core\Contracts\ErrorLog;
use Phplc\Core\Helpers;
use Phplc\Core\RuntimeFields\EventTaskFieldsCollection;

class EventProvider
{
    const REPEAT = '!&..repeat..&!';

    /** 
     * @var string[] 
     */
    private array $queue;

    public function __construct(
        private readonly ErrorLog $errLog,
    ) {
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
                Helpers::next();
                continue;
            }

            if ($this->isInnerSystemEvent($event)) {
                $innerEventExecutor($event);
                Helpers::next();
                continue;
            }

            $eventIsHandled = $collection->run($event);

            if (!$eventIsHandled) {
                $this->errLog->log(new \RuntimeException("event [{$event}] not found"));
            }

            Helpers::next();
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
}
