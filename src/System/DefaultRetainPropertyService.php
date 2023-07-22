<?php

namespace Phplc\Core\System;

use Phplc\Core\Config;
use Phplc\Core\Contracts\RetainProperty;

class DefaultRetainPropertyService extends \SQLite3 implements RetainProperty
{
    private string $table;
    private bool $init = false;

    public function __construct(
        Config $config,
    ) {
        $this->open($config->retain['dbPath']);
        $this->table = $config->retain['table'];
    }

    /** 
     * @param scalar|array|\stdClass|\JsonSerializable $value 
     */
    public function createIfNotExists(string $name, mixed $value): void
    {
        $this->createTable();
    }

    /** 
     * @param scalar|array|\stdClass|\JsonSerializable $value 
     */
    public function update(string $name, mixed $value): void
    {

    }

    /** 
     * @return scalar|array|\stdClass|\JsonSerializable 
     */
    public function select(string $name): mixed
    {
        return 2;
    }


    private function createTable(): void
    {
        if ($this->init) {
            return;
        }
        
        $query = "CREATE TABLE IF NOT EXISTS {$this->table} (
            name TEXT PRIMARY KEY
            type TEXT
            value BLOB
        );";
        $this->exec($query);
    }
}
