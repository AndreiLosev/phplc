<?php

namespace Phplc\Core\System\CommandsServer;

enum CommandResultType: string
{
    case Repeat = 'Repeat';
    case End = 'End';
    case FullEnd = 'FullEnd';

    public function isRepeat(): bool
    {
        return $this === self::Repeat;
    }

    public function isEnd(): bool
    {
        return $this === self::End;
    }

    public function isFullEnd(): bool
    {
        return $this === self::FullEnd;
    }
}
