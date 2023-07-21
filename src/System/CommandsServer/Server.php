<?php

namespace Phplc\Core\System\CommandsServer;

use Amp\ByteStream\StreamException;
use Amp\Cancellation;
use Amp\Socket;
use Phplc\Core\Container;
use Phplc\Core\System\CommandsServer\Dto\CommandFromResponse;
use Phplc\Core\System\CommandsServer\Dto\MessageFromResponse;
use function Amp\async;

class Server
{
    public const END_TOKEN = "\n#!the end!#\n";
    public const END_SESSION_TOKEN = "#!end session!#";
    public const START_MESSAGE = "waiting for the command\n";

    private bool $isEndSession = false;

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
            try {
                $socket->write($this->getFirstMessage() . self::END_TOKEN);
                $input = $socket->read();
                $this->innerHandler($socket, $input);
            } catch (StreamException $e) {
                return;
            }            
        }
    }
    
    private function innerHandler(Socket\Socket $socket, null|string $input): void
    {
        try {
            $command = $this->vlidateInput($input);
            while (true) {
                $result = $command->execute();
                $socket->write($result->message . self::END_TOKEN);

                if ($result->type->isFullEnd()) {
                    $this->isEndSession = true;
                }

                if (!$result->type->isEnd()) {
                    break;
                }

                $response = $socket->read();
                $message = MessageFromResponse::from($response);

                if (!$message->answer->isRepeat()){
                    break;
                }
            }
        } catch (ResponseException $e) {
            $socket->write($e->getMessage() . self::END_TOKEN);
        }
    }

    private function vlidateInput(string|null $input): ServerCommand {

        $command = CommandFromResponse::from($input);

        $commandObject = $this->container->make($command->command);
        $commandObject->setParams($command->params);

        return $commandObject;
    }

    private function getFirstMessage(): string
    {
        return $this->isEndSession ? self::END_SESSION_TOKEN : self::START_MESSAGE;
    }
}
