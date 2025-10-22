<?php
define("DB_HOST", "localhost");
define("DB_NAME", "kayttajat");
define("DB_USER", "root");
define("DB_PASS", "");

function getConnection(): PDO
{
    try{
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $conn = new PDO(dsn: $dsn, username: DB_USER, password: DB_PASS);
        $conn->setAtribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Yietokantavirhe " . $e->getMessage());
    }
}
?>