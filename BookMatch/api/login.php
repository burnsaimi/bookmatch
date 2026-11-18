<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);
$email = trim($dados['email'] ?? '');
$senha = $dados['senha'] ?? '';

if (!$email || !$senha) {
    http_response_code(400);
    echo json_encode(['erro' => 'Preencha todos os campos.']);
    exit;
}

try {
    $pdo  = conectar();
    $stmt = $pdo->prepare(
        "SELECT codusuario, nome, email, senha, tipo_usuario FROM usuario WHERE email = ? AND ativo = 'Ativo'"
    );
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Aceita senha em hash (cadastros novos) OU texto puro (usuários de teste do banco)
    $senhaOk = false;
    if ($usuario) {
        $hash = $usuario['senha'];
        if (password_verify($senha, $hash)) {
            $senhaOk = true;
        } elseif ($senha === $hash) {
            $senhaOk = true; // compatibilidade com seeds em texto puro
        }
    }

    if (!$usuario || !$senhaOk) {
        http_response_code(401);
        echo json_encode(['erro' => 'Usuário ou senha incorretos.']);
        exit;
    }
    unset($usuario['senha']);

    $pdo->prepare('UPDATE usuario SET data_ultimo_acesso = NOW() WHERE codusuario = ?')
        ->execute([$usuario['codusuario']]);

    echo json_encode(['sucesso' => true, 'usuario' => $usuario]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
