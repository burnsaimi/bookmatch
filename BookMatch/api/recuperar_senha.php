<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$dados     = json_decode(file_get_contents('php://input'), true);
$email     = trim($dados['email']     ?? '');
$senhaNova = $dados['senha_nova'] ?? '';

if (!$email || !$senhaNova) {
    http_response_code(400);
    echo json_encode(['erro' => 'Preencha todos os campos.']);
    exit;
}

try {
    $pdo  = conectar();
    $stmt = $pdo->prepare("SELECT codusuario FROM usuario WHERE email = ? AND ativo = 'Ativo'");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['erro' => 'E-mail não encontrado.']);
        exit;
    }

    $upd = $pdo->prepare('UPDATE usuario SET senha = ? WHERE codusuario = ?');
    $upd->execute([password_hash($senhaNova, PASSWORD_DEFAULT), $usuario['codusuario']]);

    echo json_encode(['sucesso' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
