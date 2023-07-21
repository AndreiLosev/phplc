<?php

namespace Phplc\Core\System\CommandsServer;

interface ServerCommand
{
    public function execute(): CommandResult;

    public function setParams(\stdClass $params): void;
}
