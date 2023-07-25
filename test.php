<?php

use Phplc\Core\Contracts\JsonObject;
use Phplc\Core\System\DefaultRetainPropertyService;

require_once __DIR__ . '/vendor/autoload.php';

class Test22
{
    public int $x1 = 10;

    public function getString(): string
    {
        return 'Hello world';
    }
}

class SecondT
{
    public string $prop = 'x1';
    public string $geeter = 'getString';
}

$i = new Test22;
$s = new SecondT;

print_r([
    $i->{$s->prop},
    $i->{$s->geeter}(),
]);
