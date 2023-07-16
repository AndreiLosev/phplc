<?php

namespace Phplc\Core\RuntimeFields;

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

    public function shift(): string|null
    {
        $event = array_shift($this->queue); 

        if ($event === self::REPEAT) {
            $this->queue[] = self::REPEAT;
        }

        return $event;
    }

    public function isRepeat(string $event): bool
    {
        return self::REPEAT === $event;
    }

    public function cancel(): void
    {
        $this->queue = array_filter(
            $this->queue,
            fn(string $v) => $v !== self::REPEAT,
        );   
    }
}
