<?php
// Railway usa variables de entorno para la DB
return [
    'host'     => getenv('MYSQLHOST')     ?: getenv('DB_HOST')     ?: '127.0.0.1',
    'port'     => getenv('MYSQLPORT')     ?: getenv('DB_PORT')     ?: '3306',
    'database' => getenv('MYSQLDATABASE') ?: getenv('DB_NAME')     ?: 'mercadosordo',
    'username' => getenv('MYSQLUSER')     ?: getenv('DB_USER')     ?: 'root',
    'password' => getenv('MYSQLPASSWORD') ?: getenv('DB_PASS')     ?: '',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
