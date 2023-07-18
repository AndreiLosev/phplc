<?php

namespace Phplc\Core\System\CommandsServer\Commands;

use Phplc\Core\System\CommandsServer\CommandResult;
use Phplc\Core\System\CommandsServer\CommandResultType;
use Phplc\Core\System\CommandsServer\ServerCommand;
use Phplc\Core\System\EventProvider;
use Phplc\Core\System\InnerSysteEvents;
use stdClass;

class Cansel implements ServerCommand
{
    public function __construct(
        private EventProvider $eventProvider,
    ) {}

    public function execute(): CommandResult
    {
        $this->eventProvider->dispatchEvent(InnerSysteEvents::CANSEL_EVENT);

        return new CommandResult(
            CommandResultType::End,
            'success',
        );
    }

    public function setParams(stdClass $params): void {}
}
