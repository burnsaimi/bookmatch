<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);
$codusuario = (int)($dados['usuario'] ?? 0);
$nome       = trim($dados['nome']  ?? '');
$email      = trim($dados['email'] ?? '');
$senha_nova = $dados['senha_nova'] ?? '';

if (!$codusuario || !$nome || !$email) {
    http_response_code(400);
    echo json_encode(['erro' => 'Nome e e-mail são obrigatórios.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['erro' => 'E-mail inválido.']);
    exit;
}

try {
    $pdo = conectar();

    // Verifica se o e-mail já pertence a outro usuário
    $check = $pdo->prepare('SELECT codusuario FROM usuario WHERE email = ? AND codusuario != ?');
    $check->execute([$email, $codusuario]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['erro' => 'Este e-mail já está em uso por outro usuário.']);
        exit;
    }

    if ($senha_nova) {
        $stmt = $pdo->prepare('UPDATE usuario SET nome = ?, email = ?, senha = ? WHERE codusuario = ?');
        $stmt->execute([$nome, $email, password_hash($senha_nova, PASSWORD_DEFAULT), $codusuario]);
    } else {
        $stmt = $pdo->prepare('UPDATE usuario SET nome = ?, email = ? WHERE codusuario = ?');
        $stmt->execute([$nome, $email, $codusuario]);
    }

    echo json_encode(['sucesso' => true, 'usuario' => ['codusuario' => $codusuario, 'nome' => $nome, 'email' => $email]]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
