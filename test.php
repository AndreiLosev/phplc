<?php

use Phplc\Core\Config;
use Phplc\Core\Contracts\ErrorLog;
use Phplc\Core\Contracts\JsonObject;
use Phplc\Core\Contracts\LoggingProperty;
use Phplc\Core\Contracts\RetainProperty;
use Phplc\Core\System\ChangeTrackingStorage;
use Phplc\Core\System\DefaultErrorLog;
use Phplc\Core\System\DefaultLoggingPropertyService;
use Phplc\Core\System\DefaultRetainPropertyService;
use function Amp\now;

require_once __DIR__ . '/vendor/autoload.php';

class Test22
{
    public int $x1 = 10;

    public function getString(): string
    {
        return 'Hello world';
    }
}

class SecondT
{
    public string $prop = 'x1';
    public string $geeter = 'getString';
}

$i = new Test22;
$s = new SecondT;

$build = [
    Config::class,
    ErrorLog::class => DefaultErrorLog::class,
    LoggingProperty::class => DefaultLoggingPropertyService::class,
    RetainProperty::class => DefaultRetainPropertyService::class,
    ChangeTrackingStorage::class,
];

print_r($build);
