<?php

namespace Tests\TestClsses;

use Phplc\Core\Config;

class ConfigForTests extends Config
{
    /** 
     * @var array<string, string> 
     */
    public array $retain = [
        'dbPath' => ':memory:',
        'table' => 'retain_property',
    ];

    /** 
     * @var array<string, string> 
     */
    public array $logging = [
        'dbPath' => ':memory:',
        'table' => 'logging_property',
    ];

    public float $loggingPeriod = 0.04;

    /** 
     * @var array<string, string> 
     */
    public array $errorLog = [
        'dbPath' => ':memory:',
        'table' => 'error_log',
    ];

    /** 
     * @var string[] 
     */
    public array $errorMesaageToLog = [
        'getMessage',
        // 'getCode',
        'getFile',
        'getLine',
        // 'getTrace',
        // 'getTraceAsString',
        // 'getPrevious',
        // '__toString',
    ];
}
