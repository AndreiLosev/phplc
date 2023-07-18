<?php

namespace Phplc\Core\System\CommandsServer;

use stdClass;

interface ServerCommand
{
    public function execute(): CommandResult;

    public function setParams(stdClass $params): void;
}
