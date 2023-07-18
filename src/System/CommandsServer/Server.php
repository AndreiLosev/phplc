<?php

namespace Phplc\Core\System\CommandsServer;

use Amp\Cancellation;
use Amp\Socket;
use Phplc\Core\Container;
use stdClass;
use function Amp\async;

class Server
{
    public function __construct(
        private Container $container,
        private string $socketAdr = '127.0.0.1:9191',
    ) {}

    public function lisnet(Cancellation $cancellation): void
    {
        $server = Socket\listen($this->socketAdr);

        while ($socket = $server->accept($cancellation)) {
            async(fn() => $this->handler($socket));
        }
    }

    private function handler(Socket\Socket $socket): void
    {
        while (true) {
            $input = $socket->read();
            $errorMessage = '';
            
            $command = $this->vlidateInput($input, $errorMessage);

            if (is_null($command)) {
                $socket->write($errorMessage);
                break;
            }

            $result = $command->execute();

            $socket->write($result->message);

            if ($result->type->isEnd()) {
                break;
            }
        }

        $socket->close();
    }

    private function getCommandName(string $command): string
    {
        return '\\' . __NAMESPACE__ . '\\Commands\\' . trim($command);
    }

    private function vlidateInput(
        string|null $input,
        string &$errorMessage
    ): ServerCommand|null {
        if (is_null($input)) {
                $errorMessage = "you need to send a command";
                return null;
        };

        /** @var stdClass|null */
        $inputObject = json_decode($input);
        if (is_null($inputObject)) {
            $errorMessage = "'{$input}' is invalid json";
            return null;
        }

        if (!(isset($inputObject->command) && is_string($inputObject->command))) {
            $errorMessage = "'command' must be a string";
            return null;
        }

        if (!(isset($inputObject->params) && $inputObject->params instanceof stdClass)) {
            $errorMessage = "'params' must be a object";
            return null;
        }

        $command = $this->getCommandName($inputObject->command);

        if (!(
            class_exists($command)
            && in_array(ServerCommand::class, class_implements($command))
        )) {
            $errorMessage = "'{$inputObject->command}' is unknown command";
            return null;
        }

        /** @var class-string<ServerCommand>  $command*/
        $commandObject = $this->container->make($command);
        $commandObject->setParams($inputObject->params);

        return $commandObject;
    }

}
