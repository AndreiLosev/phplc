<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Phplc\Core\Config;

class ConfigBuildTest extends TestCase
{
    public function testConfigBuildRootPath(): void
    {
        $config = new Config();
        $config->build();
        $this->assertSame($config->retain['dbPath'], '/home/andrei/prog/phplc/default.db');
        $this->assertSame($config->logging['dbPath'], '/home/andrei/prog/phplc/default.db');
        $this->assertSame($config->errorLog['dbPath'], '/home/andrei/prog/phplc/default.db');
    }
}
