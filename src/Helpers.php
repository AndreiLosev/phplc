<?php

namespace Phplc\Core;

use function Amp\delay;

class Helpers
{
    /** 
     * @param class-string $name 
     */
    public static function shortName(string $name): string
    {
        return substr(strrchr($name, '\\'), 1);
    }

    public static function next(): void
    {
        delay(0);
    }
}
