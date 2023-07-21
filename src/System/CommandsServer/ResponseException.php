<?php

namespace Phplc\Core\System\CommandsServer;

use RuntimeException;

class ResponseException extends \RuntimeException
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
