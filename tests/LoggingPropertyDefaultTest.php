<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Phplc\Core\Config;
use Phplc\Core\System\DefaultLoggingPropertyService;

class LoggingPropertyDefaultTest extends TestCase
{
    public function testLogProperty(): void
    {
        $config = new Config();
        $config->logging['dbPath'] = ':memory:';
        $rpd = new DefaultLoggingPropertyService($config);

        $step1 = [
            "\\Qwe\\Rst\\Bool::value" => false,
            "\\Qwe\\Rst\\Int::value" => 5,
            "\\Qwe\\Rst\\Float::value" => 1.2,
            "\\Qwe\\Rst\\String::value" => "hello",
        ];
        $step2 = [
            "\\Qwe\\Rst\\Bool::value" => true,
            "\\Qwe\\Rst\\Int::value" => 10,
            "\\Qwe\\Rst\\Float::value" => 2.4,
            "\\Qwe\\Rst\\String::value" => "hello, world",
        ];
        $step3 = [
            "\\Qwe\\Rst\\Bool::value" => false,
            "\\Qwe\\Rst\\Int::value" => 15,
            "\\Qwe\\Rst\\Float::value" => 3.8,
            "\\Qwe\\Rst\\String::value" => "hello, world !!",
        ];

        $keys = array_keys($step1); 
        $expect = [];
        foreach ($keys as $key) { 
            $expect[$key][0] = [$step1[$key], $step2[$key], $step3[$key]];
        }


        $rpd->build();
        $rpd->setLog($step1);
        $rpd->setLog($step2);
        $rpd->setLog($step3);

        $result = $rpd->query("SELECT * from {$config->logging['table']}");

        while ($line = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($line['name'] === $keys[0]) {
                $expect[$keys[0]][1][] = $line['value'];
            }
            if ($line['name'] === $keys[1]) {
                $expect[$keys[1]][1][] = $line['value'];
            }
            if ($line['name'] === $keys[2]) {
                $expect[$keys[2]][1][] = $line['value'];
            }
            if ($line['name'] === $keys[3]) {
                $expect[$keys[3]][1][] = $line['value'];
            }
        }

        foreach ($expect as $key => $value) {
            if ($key === "\\Qwe\\Rst\\Bool::value") {
                $this->assertSame(array_map(fn($v) => strval((int)$v), $value[0]), $value[1]);
                continue;
            }
            $this->assertSame(array_map(fn($v) => strval($v), $value[0]), $value[1]);
        }
    }
}
