<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Phplc\Core\Config;
use Phplc\Core\System\DefaultRetainPropertyService;
use Tests\TestClsses\JsonObjectForTestCase;

class RetainPropertyDefaultTest extends TestCase
{
    public function testCreateIfExists(): void
    {
        $config = new Config();
        $config->retain['dbPath'] = ':memory:';
        $rpd = new DefaultRetainPropertyService($config);

        $id = 0;
        $filds = $this->getFields();
        foreach ($filds as $key => $value) { 
            $rpd->createIfNotExists($key, $value);
            $id++;
            $this->assertSame($rpd->lastInsertRowID(), $id);
        }

        foreach ($filds as $key => $value) { 
            $rpd->createIfNotExists($key, $value);
            $this->assertSame($rpd->lastInsertRowID(), $id);
        }
        $rpd->close();
    }

    public function testSelect(): void
    {
        $config = new Config();
        $config->retain['dbPath'] = ':memory:';
        $rpd = new DefaultRetainPropertyService($config);

        $filds = $this->getFields();
        foreach ($filds as $key => $value) { 
            $rpd->createIfNotExists($key, $value);
        }

        $res = $rpd->select(array_keys($filds));

        foreach ($filds as $key => $value) {
            if (gettype($value) === 'object') {
                $this->assertSame(json_encode($res[$key]), json_encode($value));
            } else {
                $this->assertSame($res[$key], $value);
            }

        }

        $rpd->close();
    }

    public function testUpdate(): void
    {
        $config = new Config();
        $config->retain['dbPath'] = ':memory:';
        $rpd = new DefaultRetainPropertyService($config);

        $filds = $this->getFields();
        foreach ($filds as $key => $value) { 
            $rpd->createIfNotExists($key, $value);
        }

        $rpd->update('intVal', $filds['intVal']);
        $this->assertSame($rpd->changes(), 0);

        $rpd->update('accArrF', $filds['accArrF']);
        $this->assertSame($rpd->changes(), 0);

        $rpd->update('flotVal', 1.234);
        $this->assertSame($rpd->changes(), 1);

        $rpd->update('boolV', true);
        $this->assertSame($rpd->changes(), 1);

        $result = $rpd->select(['boolV', 'flotVal']);

        $this->assertSame([$result['boolV'], $result['flotVal']], [true, 1.234]);

        $rpd->close();
    }

    /** 
     * @return mixed[] 
     */
    private function getFields(): array
    {
        $filds = [
            'boolV' => false,
            'intVal' => 1569,
            'flotVal' => 95.15923,
            'arrVasl' => [1, 2, 3, 5 , 6]
        ];

        $accArrF = $filds;
        $filds['accArrF'] = $accArrF;
        $filds['obj'] = new JsonObjectForTestCase();

        return $filds;
    }
}
