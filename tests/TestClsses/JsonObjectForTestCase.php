<?php

namespace Tests\TestClsses;

use Phplc\Core\Contracts\JsonObject;

class JsonObjectForTestCase implements JsonObject
{
    public bool $boolprop = true;
    public int $intPrope = 4589;
    public array $arrPrope = ['heloo', 42, false, 'true' => 0];

    public function jsonSerialize(): mixed
    {
       return (array)$this; 
    }

    public static function fromJson(string $json): static
    {
        $arr = json_decode($json, true);
        $instans = new static(); 
        foreach ($arr as $key => $value) {
            $instans->$key = $value;
        }

        return $instans;
    }

}
