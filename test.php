<?php

use Phplc\Core\System\CommandsServer\CommandResultType;

require_once __DIR__ . '/vendor/autoload.php';

$result = array_map(
    fn(CommandResultType $v) => $v->value,
    CommandResultType::cases(),
);

var_dump(implode('|', $result));
