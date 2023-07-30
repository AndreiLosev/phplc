<?php

namespace Phplc\Core\System;

use Phplc\Core\Config;
use Phplc\Core\Contracts\LoggingProperty;

class DefaultLoggingPropertyService extends \SQLite3 implements LoggingProperty
{
    private readonly string $id;
    private readonly string $name;
    private readonly string $time;
    private readonly string $value;

    private string $table;

    public function __construct(Config $config)
    {
        $this->open($config->logging['dbPath']);
        $this->enableExceptions(true);
        $this->table = $config->logging['table'];

        $this->id = 'id';
        $this->name = 'name';
        $this->value = 'value';
        $this->time = 'time';
    }

    public function build(): void
    {
        $query = "CREATE TABLE IF NOT EXISTS {$this->table} (
            {$this->id} INTEGER PRIMARY KEY AUTOINCREMENT,
            {$this->name} TEXT,
            {$this->value} TEXT,
            {$this->time} INTEGER 
        );";
        $this->exec($query);
    }

    /** 
     * @param array<string,scalar> $property 
     */
    public function setLog(array $property): void
    {
        if (count($property) === 0) {
            return;
        }

        $sql = "
        INSERT INTO {$this->table} ({$this->name}, {$this->value}, {$this->time})
        VALUES";

        $time = time();

        foreach ($property as $key => $value) {
            $strValue = $this->toString($value);
            $sql .= "('{$key}','{$strValue}',{$time}),";
        }

        $sql[strlen($sql) - 1] = ';';

        $this->query($sql);
    }

    /** 
     * @param scalar $value 
     */
    private function toString(mixed $value): string
    {
        if (is_bool($value)) {
            return (string)(int)$value;
        }

        return (string)$value;
    }

    private function key(string $key): string
    {
        $pKey = str_replace("\\", "_", $key);
        return ":{$this->name}_{$pKey}";
    }

    private function value(string $key): string
    {
        $pValue = str_replace("\\", "_", $key);
        return ":{$this->value}_{$pValue}";
    }

    private function pLine(string $key): string
    {
        $pKey = $this->key($key);
        $pValue = $this->value($key);
        $time = time();

        return "({$pKey},{$pValue},{$time}),";
    }
}
