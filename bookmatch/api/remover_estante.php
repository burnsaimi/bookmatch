<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$dados     = json_decode(file_get_contents('php://input'), true);
$usuarioId = (int)($dados['usuario']     ?? 0);
$estanteCod= (int)($dados['estante_cod'] ?? 0);

if (!$usuarioId || !$estanteCod) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados incompletos.']);
    exit;
}

try {
    $pdo  = conectar();
    $stmt = $pdo->prepare('DELETE FROM estante WHERE cod = ? AND usuario_codusuario = ?');
    $stmt->execute([$estanteCod, $usuarioId]);
    echo json_encode(['sucesso' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
