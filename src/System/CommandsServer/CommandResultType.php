<?php

namespace Phplc\Core\System\CommandsServer;

enum CommandResultType
{
    case Repeat;
    case End;

    public function isRepeat(): bool
    {
        return $this === self::Repeat;
    }

    public function isEnd(): bool
    {
        return $this === self::End;
    }
}
