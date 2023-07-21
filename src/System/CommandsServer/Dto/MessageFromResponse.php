<?php

namespace Phplc\Core\System\CommandsServer\Dto;

use Phplc\Core\System\CommandsServer\CommandResultType;
use Phplc\Core\System\CommandsServer\ResponseException;

class MessageFromResponse 
{
    use ObjectFromJson;

    public function __construct(
        public CommandResultType $answer,
    ) {}

    public static function from(null|string $respose): MessageFromResponse
    {
        $object = self::getObjectFromJson($respose);

        if (!(property_exists($object, 'message') && is_string($object->message))) {
            throw new ResponseException("message must be a string");
        }

        $answer = CommandResultType::tryFrom($object->message);

        if (is_null($answer)) {
            $values = implode('|', array_map(
                fn(CommandResultType $v) => $v->value,
                CommandResultType::cases(),
            ));
            throw new ResponseException("message must be a {$values}");
        }


        return new MessageFromResponse($answer);
    }
}
