<?php

require_once __DIR__ . '/vendor/autoload.php';

$db = new \SQLite3('test.db');
$db->enableExceptions();

// $query = "CREATE TABLE IF NOT EXISTS TestTable (
//     name TEXT PRIMARY KEY,
//     type TEXT NOT NULL,
//     value TEXT NOT NULL
// );";

// $db->exec($query);

// $update = "
// INSERT INTO TestTable (name, type, value)
// VALUES ('v221', 'bool', 999) 
// ;";

$update = "UPDATE TestTable SET value = '1590' WHERE name = 'v221';";
$stmt = $db->prepare($update);

$sql = $stmt->getSQL();

print_r($sql);
// $stmt->bindValue(":t", 'integer', SQLITE3_TEXT);

$res = $stmt->execute();


var_dump($db->changes());

// $select = "SELECT * from TestTable";
// $res = $db->query($select);


while ($re = $res->fetchArray()) {
    print_r($re);
}
