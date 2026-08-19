<?php
namespace CT275\Labs;

use PDO;

class PDOFactory
{
    public function create(array $config): PDO
    {
        [
            'dbhost' => $dbhost,
            'dbname' => $dbname,
            'dbuser' => $dbuser,
            'dbpass' => $dbpass
        ] = $config;

        $dsn = "pgsql:host={$dbhost};dbname={$dbname};";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        return new PDO($dsn, $dbuser, $dbpass, $options);
    }
}