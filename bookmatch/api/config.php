<?php
// =============================================
// Configuração do banco de dados — PRODUÇÃO
// Servidor: http://200.132.38.218/
// =============================================
// ===== SERVIDOR DA FACULDADE (200.132.38.218) — ATIVO =====
// O PHP roda no mesmo servidor do MySQL → host local.
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bookmatch');

// ===== ENSAIO LOCAL (XAMPP da sua máquina) — para testar no seu PC =====
// Comente o bloco acima e descomente este:
// define('DB_HOST', '127.0.0.1');
// define('DB_PORT', '3307');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'bookmatch');

function conectar() {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
