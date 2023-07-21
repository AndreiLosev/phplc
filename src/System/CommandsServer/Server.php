<?php

namespace Phplc\Core\System\CommandsServer;

use Amp\Cancellation;
use Amp\Socket;
use Phplc\Core\Container;
use Phplc\Core\System\CommandsServer\Dto\CommandFromResponse;
use Phplc\Core\System\CommandsServer\Dto\MessageFromResponse;
use function Amp\async;

class Server
{
    public const END_TOKEN = "\n#!the end!#\n";
    public const START_TOKEN = "waiting for the command\n";

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
            $socket->write(self::START_TOKEN);
            $input = $socket->read();
            try {
                $command = $this->vlidateInput($input);
                while (true) {
                    $result = $command->execute();
                    $socket->write($result->message . self::END_TOKEN);

                    if ($result->type->isEnd()) {
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
    }

    private function vlidateInput(string|null $input): ServerCommand {

        $command = CommandFromResponse::from($input);

        $commandObject = $this->container->make($command->command);
        $commandObject->setParams($command->params);

        return $commandObject;
    }
}
