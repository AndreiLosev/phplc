<?php

namespace Phplc\Core\System\CommandsServer\Commands;

use Phplc\Core\RuntimeFields\EventTaskFieldsCollection;
use Phplc\Core\System\CommandsServer\CommandResult;
use Phplc\Core\System\CommandsServer\CommandResultType;
use Phplc\Core\System\CommandsServer\ServerCommand;
use Phplc\Core\System\EventProvider;

class DispatchEvent implements ServerCommand
{
    private string|null $event = null;

    public function __construct(
        private EventTaskFieldsCollection $collection,
        private EventProvider $provider,
    ) {}

    public function execute(): CommandResult
    {
        if (is_null($this->event)) {
            return new CommandResult(
                CommandResultType::End,
                '\"$params->event\" must be a string',
            );
        }

        if (!$this->collection->eventIsExists($this->event)) {
            return new CommandResult(
                CommandResultType::End,
                "event \"{$this->event}\" not found",
            );

        }
    
        $this->provider->dispatchEvent($this->event);

        return new CommandResult(
            CommandResultType::End,
            'success',
        );
    }

    public function setParams(\stdClass $params): void
    {
        if (isset($params->event) && is_string($params->event)) {
            $this->event = $params->event;
        }
    }

}
