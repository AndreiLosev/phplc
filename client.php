<?php

require_once __DIR__ . '/vendor/autoload.php';

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

function errorHeandler(Socket $socket): void
{
    $err = socket_last_error($socket);
    if ($err === 104 || $err === 32) {
        socket_close($socket);
        exit('the end');
    }
    print_r([
        $err,
        socket_strerror($err),
    ]);
}

if (!socket_connect($socket, '127.0.0.1', 9191)) {
    errorHeandler($socket);
}

function readFromSocket(Socket $socket): string
{
    $result = "";
    while (true) {
        $response = socket_read($socket, 1024, PHP_NORMAL_READ);
        if ($response === false) {
            errorHeandler($socket);
        }

        if (is_int(strpos($response, "#!the end!#\n"))) {
            $end = str_replace("#!the end!#\n", '', $response);
            $result .= $end;
            break;
        }

        if (is_int(strpos($response, "#!end session!#"))) {
            socket_close($socket);
            exit('the end');
        }

        $result .= $response;
    }

    return $result;
}

while (true) {
    
    $input = readFromSocket($socket);

    print_r($input);

    $input = readline("inpit: ");

    if (trim($input) === 'the end') {
        socket_close($socket);
        break;
    }

    $n = socket_write($socket, trim($input));

    if (!$n) {
        errorHeandler($socket);
    }

    $input = readFromSocket($socket);
    
    print_r($input . PHP_EOL);

    $message = ['message' => 'End'];
    $n = socket_write($socket, json_encode($message));

    if (!$n) {
        errorHeandler($socket);
    }
}
