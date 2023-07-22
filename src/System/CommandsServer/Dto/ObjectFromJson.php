<?php

namespace Phplc\Core\System\CommandsServer\Dto;

use Phplc\Core\System\CommandsServer\ResponseException;

trait ObjectFromJson
{
    public static function getObjectFromJson(string|null $input): \stdClass
    {
        if (is_null($input)) {
            throw new ResponseException("you need to send a not an empty message");
        };
        /** @var \stdClass|null */
        $inputObject = json_decode(trim($input));
        if (is_null($inputObject)) {
            throw new ResponseException("'{$input}' is invalid json");
        }

        return $inputObject;
    }
}
