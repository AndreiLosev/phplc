<?php

namespace Phplc\Core\System;

use Phplc\Core\Config;
use Phplc\Core\Contracts\ErrorLog;

class DefaultErrorLog extends \SQLite3 implements ErrorLog
{
    private readonly string $id;
    private readonly string $isFatal;
    private readonly string $time;
    private readonly string $error;

    private readonly string $table;

    /** 
     * @var string[] 
     */
    private readonly array $logActions;

    public function __construct(Config $config)
    {
        $this->open($config->errorLog['dbPath']);
        $this->enableExceptions(true);
        $this->table = $config->errorLog['table'];
        $this->logActions = $config->errorMesaageToLog;

        $this->id = 'id';
        $this->isFatal = 'isFatal';
        $this->time = 'time';
        $this->error = 'error';

    }

    public function build(): void
    {
        $query = "CREATE TABLE IF NOT EXISTS {$this->table} (
            {$this->id} INTEGER PRIMARY KEY AUTOINCREMENT,
            {$this->error} TEXT,
            {$this->isFatal} INTEGER,
            {$this->time} INTEGER 
        );";
        $this->exec($query);
    }

    public function log(\Throwable $e, bool $isFatal = false): void
    {
        $sql = "
            INSERT INTO {$this->table} ({$this->error}, {$this->isFatal}, {$this->time})
            VALUES (:error, :isFatal, :time);
        ";

        $stmt = $this->prepare($sql);

        $time = time();

        $stmt->bindValue(':error', $this->getStringError($e), SQLITE3_TEXT);
        $stmt->bindValue(':isFatal', (int)$isFatal, SQLITE3_INTEGER);
        $stmt->bindValue(':time', $time, SQLITE3_INTEGER);

        $stmt->execute();
    }

    private function getStringError(\Throwable $e): string|false
    {
        $result = array_map(
            fn(string $s): mixed => $this->actionsResult($s, $e),
            $this->logActions,
        );
        return json_encode($result);
    }

    private function actionsResult(string $action, \Throwable $e): mixed
    {
        if (method_exists($e, $action)) {
            return $e->$action();
        }

        return $e::class . " has no method " . $action;
    }
}
