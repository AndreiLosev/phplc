<?php

namespace tests;

use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Phplc\Core\Runtime;
use Phplc\Core\TestClsses\SimplePerioditTask;

class RuntimeBuildTest extends TestCase
{
    public function testRuntimeBuildSimplePeriodicTask(): void
    {
        $container = new Container();
        $runtime = new Runtime(
            [SimplePerioditTask::class],
            $container,
        );

        $runtime->build();

        $this->assertSame(1, 2);
    }
}
