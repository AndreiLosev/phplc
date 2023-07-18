<?php
require __DIR__ . '/vendor/autoload.php';

use Amp\DeferredCancellation;
use Amp\Socket;
use Amp\TimeoutCancellation;
use function Amp\async;
use function Amp\ByteStream\splitLines;
use function Amp\delay;

$server = Socket\listen('127.0.0.1:0');

$address = $server->getAddress();
assert($address instanceof Socket\InternetAddress);

echo 'Listening for new connections on ' . $address . ' ...' . PHP_EOL;
echo 'Connect from a terminal, e.g. ';
echo '"nc ' . $address->getAddress() . ' ' . $address->getPort() . '"' . PHP_EOL;

$token = new DeferredCancellation();

while ($socket = $server->accept($token->getCancellation())) {
    async(function () use ($socket, $token) {
        echo "Accepted connection from {$socket->getRemoteAddress()}." . PHP_EOL;

        $socket->write('hello !!!');
        $a = 0;
        while (true) {
            $mess = $inputMessage = $socket->read();
            print_r([unpack('C*', '123'), unpack('C*', trim($mess)), unpack('C*', $mess)]);
            $socket->write("you messsage: {$inputMessage}");

            if (trim($mess) === '123') {
                $token->cancel();
            }
            $a++;
        }
        $socket->end();
    });
}
