<?php

require __DIR__ . '/vendor/autoload.php';

use Amp\Socket;
use function Amp\async;
use function Amp\delay;
use function Amp\ByteStream\splitLines;
use Amp\TimeoutCancellation;

$server = Socket\listen('127.0.0.1:6666');
// $server = Socket\listen('unix:///home/andrei/prog/phplc/sock');

$server = function() use ($server) {
    while ($socket = $server->accept()) {
        async(function () use ($socket) {
            foreach (splitLines($socket) as $line) {
                $socket->write($line . PHP_EOL);
                // $socket->write($line . PHP_EOL);
            }

            $socket->end();

            echo "Closed connection to {$socket->getRemoteAddress()}." . PHP_EOL;
        });
    }
    // while (true) {
    //     $cancel = new TimeoutCancellation(0);
    //     $socket = $server->accept($cancel);
    //     if ($socket) {
    //         async(function () use ($socket) {
    //             foreach (splitLines($socket) as $line) {
    //                 print_r($line . PHP_EOL);
    //                 // $socket->write($line . PHP_EOL);
    //             }

    //             $socket->end();

    //             echo "Closed connection to {$socket->getRemoteAddress()}." . PHP_EOL;
    //         });
    //     }
    //     delay(0);
    // }
};

$print = function() {
    while (true) {
        print_r('ping ...');
        delay(2);
    }
};

$serverFuture = async($server);
$printFuture = async($print);

\Amp\Future\awaitAll([$serverFuture, $printFuture]);

