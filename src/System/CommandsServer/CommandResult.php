<?php

namespace Phplc\Core\System\CommandsServer;

class CommandResult
{
    public function __construct(
        public CommandResultType $type,
        public string $message,
    ) {}
}
