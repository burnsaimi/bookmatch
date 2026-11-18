<?php
header('Content-Type: application/json; charset=utf-8');

function conectar(): PDO
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $db   = getenv('DB_NAME') ?: 'bookmatch';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}