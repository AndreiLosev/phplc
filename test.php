<?php

use Phplc\Core\Contracts\JsonObject;

require_once __DIR__ . '/vendor/autoload.php';

class Test1 implements JsonObject {

    public int $var = 4;

    public static function fromJson(string $json): static
    {
        $arr = json_decode($json, true);
        $instants = new static();
        $instants->var = $arr['var'];

        return $instants;
    }

    public function jsonSerialize(): mixed
    {
        return $this;
    }
}

$x = new Test1;

var_dump($x);
$json = json_encode($x);
var_dump($json);
$className = Test1::class;
$x1 = $className::fromJson($json);
var_dump($x1);
