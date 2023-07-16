<?php

namespace Phplc\Core\System;

use Phplc\Core\Contracts\EventDispatcher;

class EventDispatcherDefault implements EventDispatcher
{
    /** 
     * @param \Closure(string):void $dispatch 
     */
    public function __construct(
        private \Closure $dispatch,
    ) {}

    public function dispatch(string $event): void
    {
        $fn = $this->dispatch;
        $fn($event);
    }
}
