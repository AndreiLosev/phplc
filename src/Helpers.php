<?php

namespace Phplc\Core;

class Helpers
{
    /** 
     * @param class-string $name 
     */
    public static function shortName(string $name): string
    {
        return substr(strrchr($name, '\\'), 1);
    }
}
