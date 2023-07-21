<?php

namespace Phplc\Core\System\CommandsServer\Dto;

use Phplc\Core\System\CommandsServer\ResponseException;
use Phplc\Core\System\CommandsServer\ServerCommand;
use Phplc\Core\System\CommandsServer\Commands\CommandsConstants;

class CommandFromResponse
{
    use ObjectFromJson;

    /** 
     * @param class-string<ServerCommand> $command
     */
    public function __construct(
        public string $command,
        public \stdClass $params,
    ) {}

    public static function from(null|string $respose): CommandFromResponse
    {
        $object = self::getObjectFromJson($respose);

        if (!(property_exists($object, 'command') && is_string($object->command))) {
            throw new ResponseException("'command' must be a string");
        }

        if (!(property_exists($object, 'params') && $object->params instanceof \stdClass)) {
            throw new ResponseException("'params' must be a object");
        }

        $command = self::getCommandName($object->command);

        if (!(
            class_exists($command)
            && in_array(ServerCommand::class, class_implements($command))
        )) {
            throw new ResponseException("'{$object->command}' is unknown command");
        }

        /** @var class-string<ServerCommand> $command  */
        return new CommandFromResponse($command, $object->params);
    }

    private static function getCommandName(string $command): string
    {
        return '\\' . CommandsConstants::getCommandsNamespace() . '\\' . trim($command);
    }
}
