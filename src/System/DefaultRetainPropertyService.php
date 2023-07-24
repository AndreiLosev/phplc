<?php

namespace Phplc\Core\System;

use Phplc\Core\Config;
use Phplc\Core\Contracts\JsonObject;
use Phplc\Core\Contracts\RetainProperty;

class DefaultRetainPropertyService extends \SQLite3 implements RetainProperty
{
    private readonly string $name;
    private readonly string $type;
    private readonly string $value;

    private string $table;
    private bool $init = false;

    public function __construct(Config $config)
    {
        $this->open($config->retain['dbPath']);
        $this->enableExceptions(true);
        $this->table = $config->retain['table'];

        $this->name = 'name';
        $this->value = 'value';
        $this->type = 'type';
    }

    /** 
     * @param scalar|array|JsonObject $value 
     */
    public function createIfNotExists(string $name, mixed $value): void
    {
        $this->createTable();

        [$type, $strValue] = $this->getTypeAndStringValue($value);

        $select = "
            SELECT * from {$this->table}
            WHERE {$this->name}='{$name}' AND {$this->type}='{$type}'
        ";

        $selectResult = $this->resolve($this->query($select));

        if (count($selectResult) > 0) {
            return;
        }

        $sql = "
            INSERT INTO {$this->table} ({$this->name}, {$this->type}, {$this->value})
            VALUES (:name, :type, :value); 
        ";
        $stmt = $this->prepare($sql);
        $stmt->bindValue(":name", $name, SQLITE3_TEXT);
        $stmt->bindValue(":type", $type, SQLITE3_TEXT);
        $stmt->bindValue(":value", $strValue, SQLITE3_TEXT);
        $stmt->execute();
    }

    /** 
     * @param scalar|array|JsonObject $value 
     */
    public function update(string $name, mixed $value): void
    {
        [$type, $strValue] = $this->getTypeAndStringValue($value);

        $sql = "
            UPDATE {$this->table}
            SET {$this->value} = :value
            WHERE {$this->name} = :name AND {$this->type} = :type
                AND {$this->value} != :value
        ";
        $stmt = $this->prepare($sql);
        $stmt->bindValue(":name", $name, SQLITE3_TEXT);
        $stmt->bindValue(":type", $type, SQLITE3_TEXT);
        $stmt->bindValue(":value", $strValue, SQLITE3_TEXT);

        $stmt->execute();
    }

    /** 
     * @param string[] $names 
     * @return array<string, scalar|array|JsonObject> 
     */
    public function select(array $names): mixed
    {
        $keys = implode(',', array_map(fn($v) => ":{$v}", $names));
        $sql = "SELECT * from {$this->table} WHERE name in ({$keys})";

        $stmt = $this->prepare($sql);

        for ($i = 0; $i<count($names); $i++) { 
            $stmt->bindValue(":{$names[$i]}", $names[$i], SQLITE3_TEXT);
        }

        $sqlResult = $this->resolve($stmt->execute());

        $result = [];
        for ($i = 0; $i < count($sqlResult); $i++) {
            [$name, $type, $strValue] = $sqlResult[$i];
            $value = $this->getValueFromTypeAndString($type, $strValue);
            $result[$name] = $value;
        }

        return $result;
    }


    private function createTable(): void
    {
        if ($this->init) {
            return;
        }
        
        $query = "CREATE TABLE IF NOT EXISTS {$this->table} (
            {$this->name} TEXT PRIMARY KEY,
            {$this->type} TEXT,
            {$this->value} TEXT
        );";
        $this->exec($query);
    }

    /** 
     * @param scalar|array|JsonObject $value
     * @return array{string, string}
     */
    private function getTypeAndStringValue(mixed $value): array
    {
        if ($value instanceof JsonObject) {
            return [
                "\\" . $value::class,
                json_encode($value),
            ];
        }

        if (is_array($value)) {
            return [
                gettype($value),
                json_encode($value),
            ];
        }

        if (is_bool($value)) {
            return [
                gettype($value),
                (string)(int)$value,  
            ];
        }

        return [
            gettype($value),
            (string)$value,
        ];
    }

    /** 
     * @return scalar|array|JsonObject
     */
    private function getValueFromTypeAndString(string $type, string $strValue): mixed
    {
        if (class_exists($type) && in_array(JsonObject::class, class_implements($type))) {
            /** @var class-string<JsonObject> $type*/
            $instans = $type::fromJson($strValue);
            if ($instans instanceof JsonObject) {
                return $instans;
            }
            throw new \RuntimeException("$type::fromJson does not return itself");
        }

        if ($type === 'array') {
            /** @var array|mixed */
            $arr = json_decode($strValue, true);
            if (is_array($arr)) {
                return $arr;
            }
        }

        if (in_array($type, ['integer', 'double', 'string', 'boolean'])) {
            settype($strValue, $type);
            if (is_scalar($strValue)) {
                return $strValue;
            }
        }
        
        throw new \RuntimeException(
            "retain property [{$type}] is undefined value = {$strValue}"
        );
    }

    /** 
     * @return array{string, string, string}[] - [$name, $type, $value][]
     * */
    private function resolve(\SQLite3Result $sqlResult): array
    {
        $result = [];
        while ($row = $sqlResult->fetchArray(SQLITE3_NUM)) {
            $condition = count($row) === 3 && is_string($row[0])
                && is_string($row[1]) && is_string($row[2]);
            if (!$condition) {
                throw new \RuntimeException("
                    sqlite table {$this->table} must be [name(string), type(string), value(string)]"
                );
            }
            $result[] = [$row[0], $row[1], $row[2]];
        }

        return $result;
    }
}
