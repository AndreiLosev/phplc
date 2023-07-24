<?php

namespace Phplc\Core\Contracts;

interface JsonObject extends \JsonSerializable
{
    public static function fromJson(string $json): static;
}
